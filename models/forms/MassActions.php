<?php
namespace ytubes\videos\admin\models\forms;

use Yii;
use yii\base\Model;
use ytubes\videos\models\Video;
use ytubes\videos\models\VideoStatus;
use ytubes\videos\admin\models\actions\VideoDelete;

use common\models\users\User;

/**
 * This is the model class for table "videos_import_feeds".
 *
 * @property string $name
 * @property string $delimiter
 * @property string $enclosure
 * @property string $fields
 * @property integer $skip_duplicate_urls
 * @property integer $skip_duplicate_embeds
 * @property integer $skip_new_categories
 * @property integer $external_images
 * @property string $template
 */
class MassActions extends Model
{
	/**
	 * Incoming parameter id of gallery identificators
	 * @var int
	 */
	public $user_id;
	/**
	 * Incoming parameter status of video statuses
	 * @var int
	 */
	public $status;
	/**
	 * Incoming parameter array of image identificators
	 * @var array
	 */
	public $videos_ids = [];
	/**
	 * @var integer
	 */
	private $changed_num = 0;
	/**
	 * @var string
	 */
	private $username;
	/**
	 * Response param: changed files array
	 * @var array
	 */
	public $changed_files = [];

	const SCENARIO_CHANGE_USER = 'changeUser';
	const SCENARIO_CHANGE_STATUS = 'changeStatus';

    public function rules()
    {
        return [
            [['user_id', 'status'], 'integer'],
            [['user_id'], 'required', 'on' => self::SCENARIO_CHANGE_USER],
            [['user_id'], 'userExists', 'on' => self::SCENARIO_CHANGE_USER],

            [['status'], 'required', 'on' => self::SCENARIO_CHANGE_STATUS],

            ['videos_ids', 'each', 'rule' => ['integer']],

			['videos_ids', 'filter', 'filter' => 'array_filter'],

			['videos_ids', 'required', 'message' => 'Videos not selected'],
        ];
    }
    public function scenarios()
    {
        $scenarios = parent::scenarios();
        $scenarios[self::SCENARIO_CHANGE_USER] = ['user_id', 'videos_ids'];
        $scenarios[self::SCENARIO_CHANGE_STATUS] = ['status', 'videos_ids'];
        return $scenarios;
    }
    /**
     * Валидация user_id
	 */
    public function userExists($attribute, $params, $validator)
    {
        $user = User::find()
			->where(['user_id' => $this->user_id])
			->one();

		if (!$user instanceof User) {
			$this->addError($attribute, 'Выбранного пользователя не существует');
		}

        $this->username = $user->username;
    }
	/**
	 * Изменение статуса.
	 *
	 * @return bool
	 */
	public function changeStatus()
	{
		if (!$this->validate()) {
			return false;
		}
		$status = (string) (new VideoStatus($this->status));

		$db = Yii::$app->db;
		$transaction = $db->beginTransaction();

		try {
		    $this->changed_num = $db ->createCommand()
		    	->update(Video::tableName(), ['status' => $status], '{{video_id}} IN (' . implode(',', $this->videos_ids) . ')')
		    	->execute();

			$transaction->commit();
			return true;
		} catch(\Exception $e) {
			$transaction->rollBack();
			throw $e;

			return false;
		}
	}
	/**
	 * Изменение пользователя.
	 *
	 * @return bool
	 */
	public function changeUser()
	{
		if (!$this->validate()) {
			return false;
		}

		$db = Yii::$app->db;
		$transaction = $db->beginTransaction();

		try {
		    $this->changed_num = $db->createCommand()
		    	->update(Video::tableName(), ['user_id' => $this->user_id], '{{video_id}} IN (' . implode(',', $this->videos_ids) . ')')
		    	->execute();

			$transaction->commit();
			return true;
		} catch(\Exception $e) {
			$transaction->rollBack();
			throw $e;

			return false;
		}
	}
	/**
	 * Изменение пользователя.
	 *
	 * @return bool
	 */
	public function deleteVideos()
	{
		if (!$this->validate()) {
			return false;
		}

		try {
		    $videos = Video::find()
		    	->with('images')
		    	->with('categories')
		    	->where(['video_id' => $this->videos_ids])
		    	->all();

		    foreach ($videos as $video) {
			    $action = new VideoDelete($video);

			    if ($action->isDeleted()) {
		    		$this->changed_num ++;
		    	}
			}

			return true;
		} catch(\Exception $e) {
			throw $e;

			return false;
		}
	}
	/**
	 * Возвращает количество измененных или удаленных строк.
	 *
	 * @return integer
	 */
	public function getChangedRowsNum()
	{
		return $this->changed_num;
	}
	/**
	 * Возвращает статус, если определен.
	 *
	 * @return integer|null
	 */
	public function getStatus()
	{
		return isset($this->status) ? (int) $this->status : null;
	}
	/**
	 * Текстовую версию статуса.
	 *
	 * @return string
	 */
	public function getStatusLabel()
	{
		return (new VideoStatus($this->status))->label();
	}
	/**
	 * Возвращает юзернейм нового автора.
	 *
	 * @return integer
	 */
	public function getUsername()
	{
		return $this->username;
	}
}
