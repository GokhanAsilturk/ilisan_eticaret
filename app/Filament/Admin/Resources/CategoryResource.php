<?php

namespace App\Filament\Admin\Resources;

use App\Filament\Admin\Resources\CategoryResource\Pages;
use App\Filament\Admin\Resources\CategoryResource\RelationManagers;
use App\Models\Category;
use Filament\Forms;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class CategoryResource extends Resource
{
    protected static ?string $model = Category::class;

    protected static ?string $navigationIcon = 'heroicon-o-tag';
    protected static ?string $navigationLabel = 'Kategoriler';
    protected static ?string $modelLabel = 'Kategori';
    protected static ?string $pluralModelLabel = 'Kategoriler';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Section::make('Temel Bilgiler')
                    ->schema([
                        Forms\Components\TextInput::make('name')
                            ->label('Kategori Adı')
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                if ($operation !== 'create') {
                                    return;
                                }
                                $set('slug', \Str::slug($state));
                            }),

                        Forms\Components\TextInput::make('slug')
                            ->label('URL Slug')
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText('URL için kullanılacak benzersiz isim'),

                        Forms\Components\Select::make('parent_id')
                            ->label('Üst Kategori')
                            ->relationship('parent', 'name')
                            ->searchable()
                            ->preload()
                            ->helperText('Alt kategori oluşturmak için üst kategori seçin'),

                        Forms\Components\Textarea::make('description')
                            ->label('Açıklama')
                            ->rows(3)
                            ->columnSpanFull(),
                    ])->columns(2),

                Forms\Components\Section::make('Ayarlar')
                    ->schema([
                        Forms\Components\Toggle::make('is_active')
                            ->label('Aktif')
                            ->default(true),

                        Forms\Components\TextInput::make('sort_order')
                            ->label('Sıralama')
                            ->numeric()
                            ->default(0)
                            ->helperText('Küçük sayılar önce görünür'),
                    ])->columns(2),

                Forms\Components\Section::make('SEO Ayarları')
                    ->schema([
                        Forms\Components\TextInput::make('meta_title')
                            ->label('Meta Başlık')
                            ->maxLength(255),

                        Forms\Components\Textarea::make('meta_description')
                            ->label('Meta Açıklama')
                            ->rows(3),
                    ])->collapsed(),

                Forms\Components\Section::make('Görsel')
                    ->schema([
                        Forms\Components\FileUpload::make('image_path')
                            ->label('Kategori Görseli')
                            ->image()
                            ->directory('categories')
                            ->visibility('public'),
                    ])->collapsed(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(fn (Builder $query) => $query->with(['parent']))
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label('Kategori Adı')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('parent.name')
                    ->label('Üst Kategori')
                    ->placeholder('Ana Kategori')
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_active')
                    ->label('Durum')
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle'),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label('Sıra')
                    ->numeric()
                    ->sortable(),

                Tables\Columns\TextColumn::make('products_count')
                    ->label('Ürün Sayısı')
                    ->counts('products')
                    ->sortable(),

                Tables\Columns\ImageColumn::make('image_path')
                    ->label('Görsel')
                    ->square(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Oluşturulma')
                    ->dateTime('d.m.Y H:i')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Durum')
                    ->placeholder('Tümü')
                    ->trueLabel('Aktif')
                    ->falseLabel('Pasif'),

                Tables\Filters\SelectFilter::make('parent_id')
                    ->label('Üst Kategori')
                    ->relationship('parent', 'name')
                    ->placeholder('Tümü'),
            ])
            ->actions([
                Tables\Actions\EditAction::make(),
                Tables\Actions\DeleteAction::make(),
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('sort_order');
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
            'index' => Pages\ListCategories::route('/'),
            'create' => Pages\CreateCategory::route('/create'),
            'edit' => Pages\EditCategory::route('/{record}/edit'),
        ];
    }
}
