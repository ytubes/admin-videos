<?php
namespace ytubes\videos\admin\models\forms;

use Yii;
use SplFileObject;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\StringHelper;

use ytubes\videos\admin\models\finders\CategoryFinder;
use ytubes\videos\models\Category;

/**
 * Модель для обработки формы импорта категорий через цсв файлы или просто текст.
 */
class CategoriesImport extends Model
{
    public $delimiter;
    public $enclosure;
    public $fields;

    public $csv_rows;
    public $csv_file;

    public $update_category;

    protected $model;
    /**
     * @var int $imported_rows_num количество вставленных записей.
     */
    public $imported_rows_num = 0;


    protected $options = [
        'skip' => 'Пропустить',
        'category_id' => 'id',
        'title' => 'Название',
        'slug' => 'Слаг',
        'meta_title' => 'Мета заголовок',
        'meta_description' => 'Мета описание',
        'h1' => 'Заголовок H1',
        'description' => 'Описание',
        'seotext' => 'СЕО текст',
        'param1' => 'Доп. поле 1',
        'param2' => 'Доп. поле 2',
        'param3' => 'Доп. поле 3',
    ];

    public function __construct($config = [])
    {
        parent::__construct($config);

        $this->delimiter = '|';
        $this->enclosure = '"';
        $this->fields = ['skip'];
        $this->update_category = false;
    }

    /**
     * @inheritdoc
     */
    public function rules()
    {
        return [
            [['delimiter', 'fields'], 'required'],
            ['fields', 'each', 'rule' => ['string'], 'skipOnEmpty' => false],
            [['delimiter', 'enclosure', 'csv_rows'], 'filter', 'filter' => 'trim'],
            [['delimiter', 'enclosure', 'csv_rows'], 'string'],
            [['update_category'], 'boolean'],
            ['update_category', 'default', 'value' => false],

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

                // Если загружен файл, читаем с него.
            if ($this->csv_file instanceof UploadedFile) {
                $filepath = Yii::getAlias('@runtime/tmp/' . $this->csv_file->baseName . '.' . $this->csv_file->extension);
                $this->csv_file->saveAs($filepath);

                $file = new SplFileObject($filepath);
                $file->setFlags(SplFileObject::READ_CSV|SplFileObject::READ_AHEAD|SplFileObject::SKIP_EMPTY|SplFileObject::DROP_NEW_LINE);
                $file->setCsvControl($this->delimiter, $this->enclosure);

                foreach ($file as $csvParsedString) {

                    $newCategory = [];
                    foreach ($this->fields as $key => $field) {
                        if (isset($csvParsedString[$key]) && $field !== 'skip') {
                            $newCategory[$field] = trim($csvParsedString[$key]);
                        }
                    }

                    if (empty($newCategory)) {
                        continue;
                    }

                    if ($this->insertCategory($newCategory)) {
                        $this->imported_rows_num ++;
                    }
                }

                @unlink($filepath);

                // Если файла нет, но загружено через текстовое поле, то будем читать с него.
            } elseif (!empty($this->csv_rows) || $this->csv_rows !== '') {

                $rows = explode("\n", trim($this->csv_rows, " \t\n\r\0\x0B"));

                foreach ($rows as $row) {
                    $row = trim($row, " \t\n\r\0\x0B");

                    $csvParsedString = str_getcsv($row, $this->delimiter, $this->enclosure);

                    $newCategory = [];
                    foreach ($this->fields as $key => $field) {
                        if (isset($csvParsedString[$key]) && $field !== 'skip') {
                            $newCategory[$field] = trim($csvParsedString[$key]);
                        }
                    }

                    if (empty($newCategory)) {
                        continue;
                    }

                    if ($this->insertCategory($newCategory)) {
                        $this->imported_rows_num ++;
                    }
                }
            }

            return true;
        }

        return false;
    }
    /**
     * Осуществляет вставку категории. Если таковая уже существует (чек по тайтлу и иду) то проверяется флажок, перезаписывать или нет.
     * В случае перезаписи назначает новые параметры исходя из данных файла.
     *
     * @param array $newCategory Массив с данным для вставки новой категории
     *
     * @return boolean было ли произведено обновление или вставка
     */
    protected function insertCategory($newCategory)
    {
            // Ищем, существует ли категория.
        if (!empty($newCategory['category_id'])) {
            $category = CategoryFinder::findById($newCategory['category_id']);
        } elseif (!empty($newCategory['title'])) {
            $category = CategoryFinder::findByTitle($newCategory['title']);
        } else {
            $this->addError('csv_rows', 'Требуется название или ID');
            return false;
        }

            // Если ничего не нашлось, будем вставлять новый.
        if (!($category instanceof Category)) {
            $category = new Category();
        } else {
                // Если переписывать не нужно существующую категорию, то просто проигнорировать ее.
            if ($this->update_category == false) {
                $this->addError('csv_rows', "{$category->title} дубликат");
                return false;
            }
        }

        if (isset($newCategory['category_id'])) {
            $category->category_id = (int) $newCategory['category_id'];
        }

        if (isset($newCategory['meta_description'])) {
            $newCategory['meta_description'] = StringHelper::truncate($newCategory['meta_description'], 250);
        }

        if (isset($newCategory['meta_title'])) {
            $newCategory['meta_title'] = StringHelper::truncate($newCategory['meta_title'], 255, false);
        }

        $category->attributes = $newCategory;

        if (empty($newCategory['slug']) || $newCategory['slug'] === '') {
            $category->slug = \URLify::filter($newCategory['title']);
        }

        if ($category->isNewRecord) {
            $category->updated_at = gmdate('Y:m:d H:i:s');
            $category->created_at = gmdate('Y:m:d H:i:s');
        } else {
            $category->updated_at = gmdate('Y:m:d H:i:s');
        }

        if (!$category->save(true)) {
            $attErrors = $category->getErrors();

            $errorText = '<ul>';
            foreach ($attErrors as $errors) {
                foreach ($errors as $error) {
                    $errorText .= '<li>' . $error . '</li>';
                }
            }
            $errorText .= '</ul>';

            $this->addError('csv_rows', "<b>{$category->title}</b> не сохранилось, причина: {$errorText}");

            return false;
        }

        return true;
    }

    /**
     * @return array
     */
    public function getOptions()
    {
        return $this->options;
    }
}
