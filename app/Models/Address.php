<?php

declare(strict_types=1);

namespace App\Models;

use App\Enums\AddressType;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Address extends Model
{
    use HasFactory;

    protected $fillable = [
        'user_id',
        'type',
        'label',
        'first_name',
        'last_name',
        'company',
        'phone',
        'address_line_1',
        'address_line_2',
        'district',
        'city',
        'postal_code',
        'country',
        'is_default',
        'is_active',
    ];

    protected $casts = [
        'type' => AddressType::class,
        'is_default' => 'boolean',
        'is_active' => 'boolean',
    ];

    /**
     * Model booting
     */
    protected static function boot(): void
    {
        parent::boot();

        // Default adres yapılırken diğerlerini default olmaktan çıkar
        static::saving(function (Address $address) {
            if ($address->is_default) {
                static::where('user_id', $address->user_id)
                    ->where('type', $address->type)
                    ->where('id', '!=', $address->id)
                    ->update(['is_default' => false]);
            }
        });
    }

    /**
     * İlişkiler
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Tam adı al
     */
    public function getFullNameAttribute(): string
    {
        return trim($this->first_name . ' ' . $this->last_name);
    }

    /**
     * Formatlanmış adres
     */
    public function getFormattedAddressAttribute(): string
    {
        $parts = [
            $this->full_name,
            $this->company,
            $this->address_line_1,
            $this->address_line_2,
            $this->district . '/' . $this->city,
            $this->postal_code . ' ' . $this->country,
        ];

        return implode("
", array_filter($parts));
    }

    /**
     * Tek satırda adres
     */
    public function getOneLineAddressAttribute(): string
    {
        $parts = [
            $this->address_line_1,
            $this->address_line_2,
            $this->district,
            $this->city,
            $this->postal_code,
        ];

        return implode(', ', array_filter($parts));
    }

    /**
     * Default adresi al
     */
    public static function getDefaultForUser(int $userId, AddressType $type): ?self
    {
        return static::where('user_id', $userId)
            ->where('type', $type)
            ->where('is_default', true)
            ->where('is_active', true)
            ->first();
    }

    /**
     * Array formatında adres bilgileri (sipariş için)
     */
    public function toOrderArray(): array
    {
        return [
            'first_name' => $this->first_name,
            'last_name' => $this->last_name,
            'company' => $this->company,
            'phone' => $this->phone,
            'address_line_1' => $this->address_line_1,
            'address_line_2' => $this->address_line_2,
            'district' => $this->district,
            'city' => $this->city,
            'postal_code' => $this->postal_code,
            'country' => $this->country,
        ];
    }

    /**
     * Türkiye şehir listesi (validation için)
     */
    public static function getTurkishCities(): array
    {
        return [
            'Adana', 'Adıyaman', 'Afyonkarahisar', 'Ağrı', 'Aksaray', 'Amasya', 'Ankara', 'Antalya',
            'Ardahan', 'Artvin', 'Aydın', 'Balıkesir', 'Bartın', 'Batman', 'Bayburt', 'Bilecik',
            'Bingöl', 'Bitlis', 'Bolu', 'Burdur', 'Bursa', 'Çanakkale', 'Çankırı', 'Çorum',
            'Denizli', 'Diyarbakır', 'Düzce', 'Edirne', 'Elazığ', 'Erzincan', 'Erzurum', 'Eskişehir',
            'Gaziantep', 'Giresun', 'Gümüşhane', 'Hakkari', 'Hatay', 'Iğdır', 'Isparta', 'İstanbul',
            'İzmir', 'Kahramanmaraş', 'Karabük', 'Karaman', 'Kars', 'Kastamonu', 'Kayseri', 'Kırıkkale',
            'Kırklareli', 'Kırşehir', 'Kilis', 'Kocaeli', 'Konya', 'Kütahya', 'Malatya', 'Manisa',
            'Mardin', 'Mersin', 'Muğla', 'Muş', 'Nevşehir', 'Niğde', 'Ordu', 'Osmaniye',
            'Rize', 'Sakarya', 'Samsun', 'Siirt', 'Sinop', 'Sivas', 'Şanlıurfa', 'Şırnak',
            'Tekirdağ', 'Tokat', 'Trabzon', 'Tunceli', 'Uşak', 'Van', 'Yalova', 'Yozgat', 'Zonguldak',
        ];
    }
}space App\Models;

use Illuminate\Database\Eloquent\Model;

class Address extends Model
{
    //
}
