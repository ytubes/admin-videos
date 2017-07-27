<?php
namespace ytubes\videos\admin\models\forms;

use Yii;
use SplFileObject;
use yii\base\InvalidParamException;
use yii\web\UploadedFile;
use yii\web\NotFoundHttpException;
use yii\helpers\FileHelper;
use yii\helpers\StringHelper;

use ytubes\videos\models\Video;
use ytubes\videos\models\Category;
use ytubes\videos\models\Image;
use ytubes\videos\models\RotationStats;
use ytubes\videos\admin\models\ImportFeed;
use ytubes\videos\admin\models\finders\VideoFinder;

/**
 * Пометка: Сделать проверку на соответствие полей. Если не соответствует - писать в лог.
 */

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class VideosImport extends \yii\base\Model
{
	public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_rows;
    public $csv_file;

    /**
     * @var boolean $skip_new_categories пропускать создание новых видео, если исходный урл уже есть
     */
    public $skip_duplicate_urls;
    /**
     * @var boolean $skip_new_categories пропускать создание новых видео, если emebd код такой уже есть
     */
    public $skip_duplicate_embeds;
    /**
     * @var boolean $skip_new_categories пропускать создание новых категорий
     */
    public $skip_new_categories;
    /**
     * @var boolean $external_images будут использоваться внешние тумбы или скачиваться и нарезаться на сервере.
     */
	public $external_images;
    /**
     * @var boolean пропустить первую строчку в CSV.
     */
	public $skip_first_line;
    /**
     * @var string $template шаблон вывода вставленного видео.
     */
	public $template;
	/**
	 * @var int $imported_rows_num количество вставленных записей.
	 */
	public $imported_rows_num = 0;
	/**
	 * @var array $categories категории раздела видео.
	 */
    protected $categories;
    /**
     * @var array $option опции для тега select, отвечающего за набор полей csv
     */
    protected $options;
    /**
     * @var array $not_inserted_rows забракованные строчки из CSV
     */
	protected $not_inserted_rows = [];
    /**
     * @var array $not_inserted_ids Не вставленные иды видео, если такие были.
     */
	protected $not_inserted_ids = [];
	/**
     * @var array $preset_options опции для select тега (выбор фидов вставки)
     */
    protected $preset_options;

	public function __construct(ImportFeed $importFeed, $config = [])
	{
		parent::__construct($config);

		$this->attributes = $importFeed->getAttributes();
		$this->options = $importFeed->getFieldsOptions();

		$presets = $importFeed->find()
			->select(['feed_id', 'name'])
			->asArray()
			->all();

		$options = array_column($presets, 'name', 'feed_id');
		$this->preset_options = [0 => 'Default'] + $options;

        	// Отключить логи
        if (Yii::$app->hasModule('log') && is_object(Yii::$app->log->targets['file'])) {
        	Yii::$app->log->targets['file']->enabled = false;
        }
	}

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delimiter', 'fields'], 'required'],
            ['fields', 'each', 'rule' => ['in', 'range' => array_keys($this->options)], 'skipOnEmpty' => false],
            [['delimiter', 'enclosure', 'csv_rows'], 'string'],
            [['delimiter', 'enclosure', 'csv_rows', 'template'], 'filter', 'filter' => 'trim'],
            [['skip_duplicate_urls', 'skip_duplicate_embeds', 'skip_new_categories', 'external_images', 'skip_first_line'], 'boolean'],
            [['skip_duplicate_urls', 'skip_duplicate_embeds', 'skip_new_categories', 'external_images', 'skip_first_line'], 'filter', 'filter' => function ($value) {
            	return (boolean) $value;
            }],
            [['template'], 'string', 'max' => 64],

            [['csv_file'], 'file', 'checkExtensionByMimeType' => false, 'skipOnEmpty' => true, 'extensions' => 'csv', 'maxFiles' => 1, 'mimeTypes' => 'text/plain'],
        ];
    }

    /**
     * Проверяет правильность данных в файле или текстовом поле. Затем сохраняет в базу.
     * @return boolean whether the model passes validation
     */
	public function save()
	{
		$this->csv_file = UploadedFile::getInstanceByName('csv_file');

        if ($this->validate()) {

				// Если категории заданы по ид, то у них приоритет и добавляться категории будут через иды.
			if (in_array('categories_ids', $this->fields)) {
				$this->categories = Category::find()
					->indexBy('category_id')
					->all();
			} else {
				$this->categories = Category::find()
					->indexBy('title')
					->all();
			}

				// Если загружен файл, читаем с него.
            if ($this->csv_file instanceof UploadedFile) {
            	$this->parseCsvFromFile();

                // Если файла нет, но загружено через текстовое поле, то будем читать с него.
            } elseif (!empty($this->csv_rows)) {
                $this->parseCsvFromText();
            }

            return true;
        }

			// удалим временный файл, если было загружено через него.
		if ($this->csv_file instanceof UploadedFile) {
			@unlink($this->csv_file->tempName);
		}

		return false;
	}
    /**
     * Разбор CSV из файла.
     */
    protected function parseCsvFromFile()
    {
        $fieldsNum = count($this->fields);

        $filepath = Yii::getAlias('@runtime/tmp/' . $this->csv_file->baseName . '.' . $this->csv_file->extension);
        $this->csv_file->saveAs($filepath);

        $file = new SplFileObject($filepath);
        $file->setFlags(SplFileObject::READ_CSV | SplFileObject::READ_AHEAD | SplFileObject::SKIP_EMPTY | SplFileObject::DROP_NEW_LINE);
        $file->setCsvControl($this->delimiter, $this->enclosure);

    	$startLine = 0;
    	if (true === $this->skip_first_line) {
			$startLine = 1;
    	}

        $iterator = new LimitIterator($file, $startLine);

        foreach ($iterator as $lineNumber => $csvParsedString) {
        		// Совпадает ли количество заданных полей с количеством элементов в CSV строке
        	if ($fieldsNum !== count($csvParsedString)) {
        		$row = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
        		$this->addError('csv_rows', "Строка <b class=\"text-dark-gray\">{$row}</b> не соответствует конфигурации полей");
        		continue;
        	}

            $newItem = [];
            foreach ($this->fields as $key => $field) {
                if (isset($csvParsedString[$key]) && $field !== 'skip') {
                    $newItem[$field] = trim($csvParsedString[$key]);
                }
            }

            if (empty($newItem)) {
                continue;
            }

            if (true === $this->insertItem($newItem)) {
                $this->imported_rows_num ++;
            } else {
				$this->not_inserted_rows[] = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
            }
        }

        @unlink($filepath);
    }
    /**
     * Разбор CSV из текстовой формы (textarea)
     */
    protected function parseCsvFromText()
    {
        $fieldsNum = count($this->fields);

        $rows = explode("\n", trim($this->csv_rows, " \t\n\r\0\x0B"));
        $arrayIterator = new ArrayIterator($rows);
    	$startLine = 0;

    	if (true === $this->skip_first_line) {
			$startLine = 1;
    	}

        $iterator = new LimitIterator($arrayIterator, $startLine);

        foreach ($iterator as $row) {
            $row = trim($row, " \t\n\r\0\x0B");

            $csvParsedString = str_getcsv($row, $this->delimiter, $this->enclosure);
				// Совпадает ли количество заданных полей с количеством элементов в CSV строке
        	if ($fieldsNum !== count($csvParsedString)) {
        		$this->addError('csv_rows', "Строка <b class=\"text-dark-gray\">{$row}</b> не соответствует конфигурации полей");
        		continue;
        	}

            $newItem = [];
            foreach ($this->fields as $key => $field) {
                if (isset($csvParsedString[$key]) && $field !== 'skip') {
                    $newItem[$field] = trim($csvParsedString[$key]);
                }
            }

            if (empty($newItem)) {
                continue;
            }

            if (true === $this->insertItem($newItem)) {
                $this->imported_rows_num ++;
            } else {
            	$this->not_inserted_rows[] = $row;
            }
        }
    }
	/**
	 * Осуществляет вставку видео. Если видео уже существут в базе (проверяется по source_url и embed), то вставка просто игнорируется.
	 *
	 * @param array $newVideo массив с данными для вставки нового видео.
	 * @return boolean была ли произведена вставка
	 */
	protected function insertItem($newVideo)
	{
			// Ищем, существует ли видео по иду.
		if (!empty($newVideo['video_id'])) {
			$video = VideoFinder::findById((int) $newVideo['video_id']);

			if ($video instanceof Video) {
				$this->addError('csv_rows', "{$newVideo['video_id']} дубликат идентификатора");
				return false;
			}
		}
			// Ищем, существует ли видео по урлу источника.
		if (true === $this->skip_duplicate_urls && !empty($newVideo['source_url'])) {
			$video = VideoFinder::findBySourceUrl((string) $newVideo['source_url']);

			if ($video instanceof Video) {
				$this->addError('csv_rows', "{$newVideo['source_url']} дубликат урла источника");
				return false;
			}
		}
			// Ищем, существует ли видео по embed коду.
		if (true === $this->skip_duplicate_embeds && !empty($newVideo['embed'])) {
			$video = VideoFinder::findByEmbedCode($newVideo['embed']);

			if ($video instanceof Video) {
				$this->addError('csv_rows', "{$newVideo['embed']} дубликат embed кода");
				return false;
			}
		}

			// Если ничего не нашлось, вставляем новый.
		$video = new Video();

			// Если у видео есть категории, вынесем их в отдельный массив.
		$videoCategories = [];
		if (!empty($newVideo['categories_ids'])) {
			$videoCategories = explode(',', $newVideo['categories_ids']);
			unset($newVideo['categories_ids']);

			// Или категории по названиям.
		} elseif (!empty($newVideo['categories'])) {
			$videoCategories = explode(',', $newVideo['categories']);
			unset($newVideo['categories']);
		}

			// Если у видео есть скриншоты, вынесем их в отдельный массив.
		$videoScreenshots = [];
		if (!empty($newVideo['images'])) {
			$videoScreenshots = explode(',', $newVideo['images']);
			unset($newVideo['images']);
		}


		$video->attributes = $newVideo;

		if (empty($newVideo['slug'])) {
			$slug = \URLify::filter($newVideo['title']);
		} else {
			$slug = trim($newVideo['slug']);
		}

		$video->slug = $this->generateSlug($slug);

			// Шаблон для ролика
		if (!empty($this->template)) {
			$video->template = $this->template;
		}

		$currentTime = gmdate('Y:m:d H:i:s');
		$video->updated_at = $currentTime;
		$video->created_at = $currentTime;

		if (!$video->save(true)) {
            $validateErrors = [];
			$validateErrors[$video->title] = call_user_func_array('array_merge', $video->getErrors());
            $this->addError('csv_rows', $validateErrors);

            return false;
		}

		$categories = [];
		if (!empty($videoCategories)) {

			foreach ($videoCategories as $videoCategory) {
				$categoryTitle = trim(strip_tags($videoCategory));
					// Если категории не существует и флажок "не создавать новые" выключен, добавим категорию.
				if (empty($this->categories[$categoryTitle]) && $this->skip_new_categories === false) {
					$category = new Category();

					$category->title = $categoryTitle;
					$category->slug = \URLify::filter($categoryTitle);
					$category->meta_title = $categoryTitle;
					$category->h1 = $categoryTitle;
					$category->updated_at = $currentTime;
					$category->created_at = $currentTime;
					$category->save();

					$this->categories[$categoryTitle] = $category;
				}

				if (isset($this->categories[$categoryTitle])) {
					$categories[] = $this->categories[$categoryTitle];
				}
			}
		}

		$screenshots = [];
		if (!empty($videoScreenshots)) {

			foreach ($videoScreenshots as $key => $videoScreenshot) {
				$screenshot = new Image();

				$screenshot->video_id = $video->video_id;
				$screenshot->position = $key;
				$screenshot->source_url = trim($videoScreenshot);
				$screenshot->created_at = $currentTime;

				if (true === $this->external_images) {
					$screenshot->status = 10;
					$screenshot->filepath = trim($videoScreenshot);
				} else {
					$screenshot->status = 0;
				}

				if ($screenshot->save(true)) {
					$screenshots[] = $screenshot;
					if ($key === 0) {
						$video->link('image', $screenshot);
					}
				}
			}
		}

			// В таблицу для ротации
		if (!empty($categories) && !empty($screenshots)) {
			foreach ($categories as $category) {
				foreach ($screenshots as $sKey => $screenshot) {
					$rotationStats = RotationStats::find()
						->where(['video_id' => $video->video_id, 'category_id' => $category->category_id, 'image_id' => $screenshot->image_id])
						->one();

					if ($rotationStats instanceof RotationStats)
						continue;

					$rotationStats = new RotationStats();

					$rotationStats->video_id = $video->video_id;
					$rotationStats->category_id = $category->category_id;
					$rotationStats->image_id = $screenshot->image_id;
					$rotationStats->published_at = $video->published_at;
					$rotationStats->duration = (int) $video->duration;

					if ($sKey === 0) {
						$rotationStats->best_image = 1;
					}

					$rotationStats->save();
				}
			}
		}

		unset($screenshots, $categories);

		return true;
	}

	/**
	 * Осуществляет вставку категории. Если таковая уже существует (чек по тайтлу и иду) то проверяется флажок, перезаписывать или нет.
	 *
	 * В случае перезаписи назначает новые параметры исходя из данных файла.
	 *
	 * @return boolean было ли произведено обновление или вставка
	 */
	protected function insertCategory($newCategory)
	{
	}

	/**
	 * Генерирует slug исходя из title. Также присоединяет численный суффикс, если слаг не уникален.
	 *
	 * @param string $title
	 * @return string
	 */
	private function generateSlug($title)
	{
		$slug = \URLify::filter($title);

		if (!$slug)
			$slug = 'default-video';

		if ($this->checkUniqueSlug($slug)) {
			return $slug;
		} else {
			for ($suffix = 1; !$this->checkUniqueSlug($new_slug = $slug . '-' . $suffix); $suffix++ ) {}
			return $new_slug;
		}
	}

	/**
	 * Проверяет является ли slug уникальным. Верент true, если уникален.
	 *
	 * @param string $slug
	 * @return bool
	 */
	private function checkUniqueSlug($slug)
	{
		$sql = "SELECT `video_id` FROM `" . Video::tableName() . "` WHERE `slug`='{$slug}'";

		$id = Video::getDb()->createCommand($sql)
           ->queryOne();

		return false === $id;
	}

	/**
	 * @return array
	 */
	public function getOptions()
	{
		return $this->options;
	}

	/**
	 * @return array
	 */
	public function getPresetOptions()
	{
		return $this->preset_options;
	}
}
