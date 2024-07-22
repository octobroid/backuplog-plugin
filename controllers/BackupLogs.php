<?php namespace Octobro\BackupLog\Controllers;

use ApplicationException, Artisan, BackendMenu, Flash, Storage;
use Backend\Classes\Controller;
use System\Classes\SettingsManager;
use Octobro\BackupLog\Models\BackupLog;

/**
 * Backup Logs Backend Controller
 *
 * @link https://docs.octobercms.com/3.x/extend/system/controllers.html
 */
class BackupLogs extends Controller
{
    public $implement = [
        \Backend\Behaviors\FormController::class,
        \Backend\Behaviors\ListController::class
    ];

    /**
     * @var string formConfig file
     */
    public $formConfig = 'config_form.yaml';

    /**
     * @var string listConfig file
     */
    public $listConfig = 'config_list.yaml';

    /**
     * @var array required permissions
     */
    public $requiredPermissions = ['octobro.backuplog.backuplogs'];

    /**
     * __construct the controller
     */
    public function __construct()
    {
        parent::__construct();

        BackendMenu::setContext('October.System', 'system', 'settings');
        SettingsManager::setContext('Octobro.BackupLog', 'backup_logs');
    }

    public function index($recordId = null)
    {
        $this->vars['record_id'] = $recordId;
        return $this->asExtension('ListController')->index();
    }

    /**
     * index_onRefresh
     */
    public function index_onRefresh()
    {
        return $this->listRefresh();
    }

    public function onBackupDatabase()
    {
        $filename = 'octobro_backup_db_' . now()->format('jMy_H-i') . '.zip';
        $command  = sprintf('backup:run --only-db --filename=%s', $filename);

        Artisan::call($command);
        BackupLog::record($filename);

        Flash::success('Backup Successfully Updated');
        return redirect()->refresh();
    }

    public function onDownloadBackup()
    {
        try {
            $model = $this->formFindModelObject(request()->get('backup_id'));
            throw_if(!$model->is_file_exist, ApplicationException::class, 'Backup file seems not exists anymore, for sake of definite existence we remove this backup records.');
            Flash::info(sprintf('%s %s', 'Downloading', $model->name));

            return $this->downloadBackup($model);
        } catch (ApplicationException $th) {
            if(!data_get($model, 'is_file_exist')){
                $model->delete();
            }

            Flash::error($th->getMessage());

            return redirect()->refresh();
        }
    }

    protected function downloadBackup($model)
    {
        try {
            $headers = [
                "Cache-Control"       => 'public',
                "Content-Description" => 'File Transfer',
                "Content-Disposition" => 'attachment; filename=' . $model->name,
                "Content-Type"        => $model->mime_type,
            ];

            if(config('filesystems.default') != 's3'){
                return response()->make(file_get_contents($model->path), 200, $headers);
            }

            return response()->make(file_get_contents(Storage::url($model->path)), 200, $headers);
        } catch (ApplicationException $th) {
            throw $th;
        }
    }
}
