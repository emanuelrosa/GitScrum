<?php
/**
 * GitScrum v0.1
 *
 * @package  GitScrum
 * @author  Renato Marinho <renato.marinho@s2move.com>
 * @license http://opensource.org/licenses/GPL-3.0 GPLv3
 */

namespace GitScrum\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;
use GitScrum\Classes\Helper;
use Auth;

class ProductBacklog extends Model {

    use SoftDeletes;

    /**
     * The database table used by the model.
     *
     * @var string
     */
    protected $table = 'product_backlogs';

    /**
     * Attributes that should be mass-assignable.
     *
     * @var array
     */
    protected $fillable = ['github_id', 'organization_id', 'slug', 'title', 'description',
        'fullname', 'private', 'html_url', 'description', 'fork', 'url', 'since', 'pushed_at',
        'git_url', 'ssh_url', 'clone_url', 'homepage', 'default_branch'];

    /**
     * The attributes excluded from the model's JSON form.
     *
     * @var array
     */
    protected $hidden = [];

    /**
     * The attributes that should be casted to native types.
     *
     * @var array
     */
    protected $casts = ['private' => 'boolean', 'fork' => 'boolean'];

    protected static function boot()
    {
        parent::boot();
    }

    public function organization()
    {
        return $this->hasOne(\GitScrum\Models\Organization::class, 'id', 'organization_id');
    }

    public function sprints()
    {
        return $this->hasMany(\GitScrum\Models\Sprint::class, 'product_backlog_id', 'id');
    }

    public function issues() {
        return $this->hasMany(\GitScrum\Models\Issue::class, 'product_backlog_id', 'id')
            ->orderby('position', 'ASC');
    }

    public function userStories()
    {
        return $this->hasMany(\GitScrum\Models\UserStory::class, 'product_backlog_id', 'id');
    }

    public function attachments()
    {
        return $this->morphMany(\GitScrum\Models\Attachment::class, 'attachmentable');
    }

    public function notes()
    {
        return $this->morphMany(\GitScrum\Models\Note::class, 'noteable')
            ->orderby('position', 'ASC');
    }

    public function favorite()
    {
        return $this->morphOne(\GitScrum\Models\Favorite::class, 'favoriteable');
    }

    public function comments()
    {
        return $this->morphMany(\GitScrum\Models\Comment::class, 'commentable')
            ->orderby('created_at', 'DESC');
    }

    public function notesPercentComplete()
    {
        $total = $this->notes->count();
        $totalClosed = $total-$this->notes->where('closed_at', NULL)->count();

        return ($totalClosed) ? ceil(($totalClosed * 100) / $total) : 0;
    }

    public function getVisibilityAttribute()
    {
        return $this->attributes['is_private']?_('Private'):_('Public');
    }

}
