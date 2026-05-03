<?php

namespace App\Models;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Job extends Model
{
    use HasFactory;
    protected $fillable = [
        'user_id','category_id', 'title','description','location','type','salary_min','salary_max','status','expire_at'];
    public function employer()
    {
        return $this->belongsTo(User::class,'user_id');
    }
    public function category()
    {
        return $this->belongsTo(Category::class);
    }
    public function applications()
    {
        return $this->hasMany(JobApplication::class);
    }
}
