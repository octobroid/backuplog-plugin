<?php namespace Octobro\BackupLog\Models;

use Model;
use Storage;

/**
 * BackupLog Model
 *
 * @link https://docs.octobercms.com/3.x/extend/system/models.html
 */
class BackupLog extends Model
{
    use \October\Rain\Database\Traits\Validation;

    /**
     * @var string table name
     */
    public $table = 'octobro_backuplog_backup_logs';

    protected $guarded = ['id'];

    protected $fillable = [];

    /**
     * @var array rules for validation
     */
    public $rules = [];

    // This function is associate by Spatie Backup
    public static function record($filename)
    {
        $existing_data = [
            'name' => 'backup_' . $filename
        ];

        $file_path = sprintf('%s/backup_%s', config('backup.backup.name'), $filename);

        if(Storage::exists($file_path)){
            static::updateOrCreate($existing_data, [
                'path'          => Storage::path($file_path),
                'size'          => Storage::size($file_path),
                'mime_type'     => Storage::mimeType($file_path),
                'last_modified' => Storage::lastModified($file_path)
            ]);
        }
    }

    public function getIsFileExistAttribute()
    {
        return Storage::exists(sprintf('%s/%s', config('backup.backup.name'), $this->name));
    }
}
