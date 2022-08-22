<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Support\Str;
use App\Enum\UserWalletTransactionTypeEnum;

/**
 * Class User wallet transaction.
 *
 * @property int         $user_wallet_id
 * @property string      $uuid
 * @property string      $type
 * @property float       $amount
 * @property string      $comment
 */
class UserWalletTransaction extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'uuid',
        'user_id',
        'type',
        'amount',
        'comment'
    ];

    /**
     * The attributes that should be cast to native types.
     *
     * @var array
     */
    protected $casts = [
        'user_id' => 'int',
    ];

    /**
     * @return BelongsTo
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * @param $value
     * 
     * @return void
     */
    public function setUuidAttribute($value): void 
    {
        $this->attributes['uuid'] = (string) Str::uuid();
    }

    /**
     * @return $amount
     */
    public function getAmountWithSymbolAttribute()
    {
        $operator = null;
        
        if ($this->type == UserWalletTransactionTypeEnum::DEPOSIT->value) {
            $operator = '+';
        } else if ($this->type == UserWalletTransactionTypeEnum::WITHDRAW->value) {
            $operator = '-';
        }

        return $operator . ' ' . $this->amount;
    }
}
