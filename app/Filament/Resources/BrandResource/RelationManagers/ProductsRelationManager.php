<?php

namespace App\Filament\Resources\BrandResource\RelationManagers;

use App\Enums\ProductTypeEnum;
use App\Models\Product;
use Illuminate\Support\Str;

use Filament\Actions\ActionGroup;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\RelationManagers\RelationManager;
use Filament\Tables;
use Filament\Tables\Columns\ColorColumn;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductsRelationManager extends RelationManager
{
    protected static string $relationship = 'products';

    public function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Tabs::make('products')
                    ->tabs([
                        Forms\Components\Tabs\Tab::make('information')
                            ->schema([
                                TextInput::make('name')
                                    ->required()
                                    ->unique(ignoreRecord: true)
                                    ->live(onBlur: true)
                                    ->afterStateUpdated(function (string $operation, $state, Forms\Set $set) {
                                        if ($operation !== 'create') {
                                            return;
                                        }
                                        $set('slug', Str::slug($state));
                                    }),
                                TextInput::make('slug')
                                    ->disabled()
                                    ->dehydrated()
                                    ->required()
                                    ->unique(Product::class, 'slug', ignoreRecord: true),
                                TextInput::make('description')->columnSpan('full')
                            ])->columns(2),
                        Forms\Components\Tabs\tab::make('Pricing & inventory')
                            ->schema([
                                TextInput::make('price')
                                    ->numeric()
                                    ->rules(['regex:/^\d{1,6}(\.\d{0,2})?$/'])
                                    ->required()
                                    ->requiredUnless('field', 'value'),
                                TextInput::make('sku')
                                    ->label('SKU(Stock Keeping Unit')
                                    ->unique(ignoreRecord: true)
                                    ->required(),
                                TextInput::make('quantity')
                                    ->numeric()
                                    ->minValue(0)
                                    ->required(),
                                Radio::make('type')
                                    ->options([
                                        'downloadable' => ProductTypeEnum::DOWNLOADABLE->value,
                                        'deliverable' => ProductTypeEnum::DELIVERABLE->value,
                                    ])->required()
                            ]),
                        Forms\Components\Tabs\tab::make('additional information')
                            ->schema([
                                Toggle::make('is_visable')
                                    ->label('visibility')
                                    ->helperText('enable or disable product visibility')
                                    ->default(true),
                                Toggle::make('is_featured')
                                    ->label('is featured')
                                    ->helperText('Enable or Disable featured status'),
                                DatePicker::make('published_at')
                                    ->label('aviliability')
                                    ->default(now()),
                                Select::make('categories')
                                    ->relationship('categories', 'name')
                                    ->multiple()
                                    ->required(),

                                FileUpload::make('image')
                                    ->image()
                                    ->imageEditor()
                                    ->preserveFilenames()
                                    ->directory('form-attachment')
                                    ->columnSpanFull()
                            ])->columns(2)

                    ])->columnSpanFull()
            ]);
    }

    public function table(Table $table): Table
    {
        return $table
            ->recordTitleAttribute('name')
            ->columns([
                ImageColumn::make('image'),
                TextColumn::make('name')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('brand.name')
                    ->sortable()
                    ->searchable()
                    ->toggleable(),
                IconColumn::make('is_visable')
                    ->boolean()
                    ->sortable()
                    ->toggleable()
                    ->label('visibility'),
                TextColumn::make('price')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('quantity')
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('published_at')
                    ->date()
                    ->sortable(),
                TextColumn::make('type')
            ])
            ->filters([
                //
            ])
            ->headerActions([
                Tables\Actions\CreateAction::make(),
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                ])
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
            ]);
    }
}
