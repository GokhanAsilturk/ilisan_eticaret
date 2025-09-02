<?php

namespace App\Filament\Admin\Resources;

use App\Enums\OrderStatus;
use App\Enums\PaymentStatus;
use App\Filament\Admin\Resources\OrderResource\Pages;
use App\Models\Order;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Enums\FontWeight;
use Filament\Tables\Filters\Filter;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-shopping-bag';

    protected static ?string $navigationLabel = 'Siparişler';

    protected static ?string $modelLabel = 'Sipariş';

    protected static ?string $pluralModelLabel = 'Siparişler';

    protected static ?int $navigationSort = 2;

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Sipariş Bilgileri')
                    ->schema([
                        Forms\Components\Select::make('user_id')
                            ->label('Müşteri')
                            ->relationship('user', 'name')
                            ->searchable()
                            ->preload(),

                        Forms\Components\TextInput::make('order_number')
                            ->label('Sipariş No')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true),

                        Forms\Components\Select::make('status')
                            ->label('Sipariş Durumu')
                            ->options(OrderStatus::getOptions())
                            ->required()
                            ->default(OrderStatus::PENDING->value)
                            ->native(false),

                        Forms\Components\Select::make('payment_status')
                            ->label('Ödeme Durumu')
                            ->options(PaymentStatus::getOptions())
                            ->required()
                            ->default(PaymentStatus::PENDING->value)
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('İletişim Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('email')
                            ->label('E-posta')
                            ->email()
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('phone')
                            ->label('Telefon')
                            ->tel()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('first_name')
                            ->label('Ad')
                            ->required()
                            ->maxLength(255),

                        Forms\Components\TextInput::make('last_name')
                            ->label('Soyad')
                            ->required()
                            ->maxLength(255),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Fiyat Bilgileri')
                    ->schema([
                        Forms\Components\TextInput::make('subtotal')
                            ->label('Ara Toplam')
                            ->required()
                            ->numeric()
                            ->prefix('₺')
                            ->default(0),

                        Forms\Components\TextInput::make('tax_total')
                            ->label('Vergi Toplamı')
                            ->required()
                            ->numeric()
                            ->prefix('₺')
                            ->default(0),

                        Forms\Components\TextInput::make('shipping_total')
                            ->label('Kargo Ücreti')
                            ->required()
                            ->numeric()
                            ->prefix('₺')
                            ->default(0),

                        Forms\Components\TextInput::make('total')
                            ->label('Genel Toplam')
                            ->required()
                            ->numeric()
                            ->prefix('₺')
                            ->default(0),

                        Forms\Components\Select::make('currency')
                            ->label('Para Birimi')
                            ->required()
                            ->options([
                                'TRY' => 'Türk Lirası (₺)',
                                'USD' => 'Amerikan Doları ($)',
                                'EUR' => 'Euro (€)',
                            ])
                            ->default('TRY')
                            ->native(false),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Tarihler')
                    ->schema([
                        Forms\Components\DateTimePicker::make('placed_at')
                            ->label('Sipariş Tarihi')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('shipped_at')
                            ->label('Kargoya Verilme Tarihi')
                            ->native(false),

                        Forms\Components\DateTimePicker::make('delivered_at')
                            ->label('Teslim Tarihi')
                            ->native(false),
                    ])
                    ->columns(3),

                Forms\Components\Section::make('Adresler')
                    ->schema([
                        Forms\Components\Textarea::make('billing_address')
                            ->label('Fatura Adresi')
                            ->required()
                            ->rows(3),

                        Forms\Components\Textarea::make('shipping_address')
                            ->label('Teslimat Adresi')
                            ->required()
                            ->rows(3),
                    ])
                    ->columns(2),

                Forms\Components\Section::make('Notlar ve Ek Bilgiler')
                    ->schema([
                        Forms\Components\Textarea::make('notes')
                            ->label('Notlar')
                            ->rows(3)
                            ->columnSpanFull(),

                        Forms\Components\KeyValue::make('metadata')
                            ->label('Metadata')
                            ->columnSpanFull(),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('order_number')
                    ->label('Sipariş No')
                    ->searchable()
                    ->sortable()
                    ->weight(FontWeight::Bold)
                    ->copyable(),

                Tables\Columns\TextColumn::make('user.name')
                    ->label('Müşteri')
                    ->searchable()
                    ->sortable()
                    ->limit(30),

                Tables\Columns\BadgeColumn::make('status')
                    ->label('Sipariş Durumu')
                    ->formatStateUsing(fn (string $state): string => OrderStatus::from($state)->label())
                    ->colors([
                        'warning' => OrderStatus::PENDING->value,
                        'info' => [OrderStatus::PAID->value],
                        'primary' => OrderStatus::PROCESSING->value,
                        'secondary' => OrderStatus::SHIPPED->value,
                        'success' => OrderStatus::DELIVERED->value,
                        'danger' => OrderStatus::CANCELLED->value,
                        'gray' => OrderStatus::REFUNDED->value,
                    ]),

                Tables\Columns\BadgeColumn::make('payment_status')
                    ->label('Ödeme Durumu')
                    ->formatStateUsing(fn (string $state): string => PaymentStatus::from($state)->label())
                    ->colors([
                        'warning' => PaymentStatus::PENDING->value,
                        'info' => [PaymentStatus::PROCESSING->value, PaymentStatus::AUTHORIZED->value],
                        'success' => PaymentStatus::CAPTURED->value,
                        'danger' => PaymentStatus::FAILED->value,
                        'gray' => PaymentStatus::REFUNDED->value,
                        'secondary' => PaymentStatus::CANCELLED->value,
                    ]),

                Tables\Columns\TextColumn::make('total')
                    ->label('Toplam')
                    ->money('TRY')
                    ->sortable()
                    ->alignEnd()
                    ->weight(FontWeight::Bold),

                Tables\Columns\TextColumn::make('items_count')
                    ->label('Ürün Sayısı')
                    ->counts('items')
                    ->badge()
                    ->color('primary'),

                Tables\Columns\TextColumn::make('email')
                    ->label('E-posta')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('phone')
                    ->label('Telefon')
                    ->searchable()
                    ->toggleable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('placed_at')
                    ->label('Sipariş Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('shipped_at')
                    ->label('Kargo Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('delivered_at')
                    ->label('Teslimat Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturma Tarihi')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->defaultSort('created_at', 'desc')
            ->filters([
                SelectFilter::make('status')
                    ->label('Sipariş Durumu')
                    ->options(OrderStatus::getOptions())
                    ->native(false),

                SelectFilter::make('payment_status')
                    ->label('Ödeme Durumu')
                    ->options(PaymentStatus::getOptions())
                    ->native(false),

                Filter::make('placed_today')
                    ->label('Bugün Verilen Siparişler')
                    ->query(fn (Builder $query): Builder => $query->whereDate('placed_at', today())),

                Filter::make('high_value')
                    ->label('Yüksek Değerli Siparişler (>1000₺)')
                    ->query(fn (Builder $query): Builder => $query->where('total', '>', 1000)),

                Filter::make('pending_shipment')
                    ->label('Kargoya Verilebilir')
                    ->query(fn (Builder $query): Builder => $query
                        ->where('status', OrderStatus::PAID->value)
                        ->whereNull('shipped_at')),
            ])
            ->actions([
                Tables\Actions\ViewAction::make(),
                Tables\Actions\EditAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\BulkAction::make('mark_as_processing')
                        ->label('İşleniyor Olarak İşaretle')
                        ->icon('heroicon-o-clock')
                        ->color('primary')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update(['status' => OrderStatus::PROCESSING]))),

                    Tables\Actions\BulkAction::make('mark_as_shipped')
                        ->label('Kargoya Verildi Olarak İşaretle')
                        ->icon('heroicon-o-truck')
                        ->color('secondary')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update([
                            'status' => OrderStatus::SHIPPED,
                            'shipped_at' => now(),
                        ]))),

                    Tables\Actions\BulkAction::make('mark_as_delivered')
                        ->label('Teslim Edildi Olarak İşaretle')
                        ->icon('heroicon-o-check-circle')
                        ->color('success')
                        ->action(fn ($records) => $records->each(fn ($record) => $record->update([
                            'status' => OrderStatus::DELIVERED,
                            'delivered_at' => now(),
                        ]))),

                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}
