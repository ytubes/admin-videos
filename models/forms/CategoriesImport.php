<?php
namespace ytubes\videos\admin\models\forms;

use Yii;
use SplFileObject;
use ArrayIterator;
use LimitIterator;
use yii\base\Model;
use yii\web\UploadedFile;
use yii\helpers\StringHelper;
use yii\helpers\FileHelper;

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
    public $skip_first_line;

    public $csv_rows;
    public $csv_file;

    public $update_category;

    protected $not_inserted_rows = [];

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
        $this->skip_first_line = true;
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
            [['delimiter', 'enclosure', 'csv_rows'], 'filter', 'filter' => 'trim'],
            [['delimiter', 'enclosure', 'csv_rows'], 'string'],
            [['update_category', 'skip_first_line'], 'boolean'],
            [['update_category', 'skip_first_line'], 'filter', 'filter' => function ($value) {
                return (boolean) $value;
            }],
            ['update_category', 'default', 'value' => false],
            ['skip_first_line', 'default', 'value' => true],

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

        $filename = $this->csv_file->baseName . '.' . $this->csv_file->extension;

        $filepath = Yii::getAlias("@runtime/tmp/{$filename}");

        if (!is_dir(dirname($filepath))) {
            FileHelper::CreateDirectory(dirname($filepath), 0755);
        }

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
            $elementsNum = count($csvParsedString);
            if ($fieldsNum !== $elementsNum) {
                $row = $this->str_putcsv($csvParsedString, $this->delimiter, $this->enclosure);
                $this->addError('csv_rows', "Строка <b class=\"text-dark-gray\">{$row}</b> не соответствует конфигурации колонок. Количество полей указано: {$fieldsNum}, фактическое количество колонок: {$elementsNum}");
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
            $elementsNum = count($csvParsedString);
            if ($fieldsNum !== $elementsNum) {
                $this->addError('csv_rows', "Строка <b class=\"text-dark-gray\">{$row}</b> не соответствует конфигурации колонок. Количество полей указано: {$fieldsNum}, фактическое количество колонок: {$elementsNum}");
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
     * Осуществляет вставку категории. Если таковая уже существует (чек по тайтлу и иду) то проверяется флажок, перезаписывать или нет.
     * В случае перезаписи назначает новые параметры исходя из данных файла.
     *
     * @param array $newCategory Массив с данным для вставки новой категории
     *
     * @return boolean было ли произведено обновление или вставка
     */
    protected function insertItem($newCategory)
    {
        $currentTime = gmdate('Y-m-d H:i:s');

            // Ищем, существует ли категория.
        if (!empty($newCategory['category_id'])) {
            $category = CategoryFinder::findById($newCategory['category_id']);
        } elseif (!empty($newCategory['title'])) {
            $category = CategoryFinder::findByTitle($newCategory['title']);
        } else {
            $this->addError('csv_rows', 'Требуется название или ID');
            return false;
        }

            // Если название все таки пустое, значит оно будет идом категории.
        if (empty($newCategory['title'])) {
            $newCategory['title'] = $newCategory['category_id'];
        }

            // Если ничего не нашлось, будем вставлять новый.
        if (!$category instanceof Category) {
            $category = new Category();
        } else {
                // Если переписывать не нужно существующую категорию, то просто проигнорировать ее.
            if (false === $this->update_category) {
                $this->addError('csv_rows', "<b>{$category->title}</b> дубликат");
                return false;
            }
        }

        if (isset($newCategory['category_id'])) {
            $category->category_id = (int) $newCategory['category_id'];
        }

        if (isset($newCategory['meta_description'])) {
            $newCategory['meta_description'] = StringHelper::truncate($newCategory['meta_description'], 250, false);
        }

        if (isset($newCategory['meta_title'])) {
            $newCategory['meta_title'] = StringHelper::truncate($newCategory['meta_title'], 250, false);
        }

        $category->attributes = $newCategory;

        $slug = empty($newCategory['slug']) ? $newCategory['title'] : $newCategory['slug'];
        $category->generateSlug($slug);

        if ($category->isNewRecord) {
            $category->updated_at = $currentTime;
            $category->created_at = $currentTime;
        } else {
            $category->updated_at = $currentTime;
        }

        if (!$category->save(true)) {
            $validateErrors = [];
            $validateErrors[$category->title] = call_user_func_array('array_merge', $category->getErrors());
            $this->addError('csv_rows', $validateErrors);

            return false;
        }

        return true;
    }
    /**
     * Собирает CSV строчку из массива.
     *
     * @param array $input
     * @param string $delimiter
     * @param string $enclosure
     * @return string
     */
    protected function str_putcsv($input, $delimiter = ',', $enclosure = '"')
    {
        $fp = fopen('php://temp', 'r+');
        fputcsv($fp, $input, $delimiter, $enclosure);
        rewind($fp);
        $data = fread($fp, 1048576);
        fclose($fp);
        return rtrim($data, "\n");
    }
    /**
     * @inheritdoc
     */
    public function hasNotInsertedRows() {
        return !empty($this->not_inserted_rows);
    }
    /**
     * @inheritdoc
     */
    public function getNotInsertedRows()
    {
        return $this->not_inserted_rows;
    }
    /**
     * @inheritdoc
     */
    public function getOptions()
    {
        return $this->options;
    }
}
