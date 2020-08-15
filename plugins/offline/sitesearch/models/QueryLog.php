<?php namespace OFFLINE\SiteSearch\Models;

use Cache;
use Model;

/**
 * Model
 */
class QueryLog extends Model
{
    use \October\Rain\Database\Traits\Validation;

    public $timestamps = false;

    public $table = 'offline_sitesearch_query_logs';
    public $fillable = [
        'query',
        'domain',
        'location',
        'useragent',
    ];
    public $rules = [
        'query' => 'required',
    ];

    public function beforeCreate()
    {
        $this->domain     = parse_url(request()->fullUrl(), PHP_URL_HOST);
        $this->location   = request()->getPathInfo();
        $this->useragent  = request()->userAgent();
        $this->session_id = session()->getId();
        $this->created_at = now();
    }

    /**
     * Cleanup old search queries.
     *
     * This method is called with each search. Cleanup happens
     * once a day at most.
     */
    public static function cleanup()
    {
        if (Cache::has('sitesearch.cleanup_locked')) {
            return;
        }

        Cache::put('sitesearch.cleanup_locked', true, now()->addDay());

        $deadline = now()->subDays((int)Settings::get('log_keep_days', 365));

        self::where('created_at', '<=', $deadline)->delete();
    }
}
