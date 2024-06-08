<?php

namespace App\Filament\Resources;

use Filament\Tables\Filters\SelectFilter;

use App\Enums\ProductTypeEnum;
use App\Filament\Resources\ProductResource\Pages;
use App\Filament\Resources\ProductResource\RelationManagers;
use App\Models\Product;
use DeepCopy\Filter\Filter;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\FileUpload;
use Filament\Forms\Components\Group;
use Filament\Forms\Components\Radio;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\IconColumn;
use Filament\Tables\Columns\ImageColumn;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\TernaryFilter;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Str;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?string $navigationGroup = 'shop';
    protected static ?int $navigationSort = 0;

    protected static ?string $recordTitleAttribute = 'name';
    public static function getNavigationbadge(): ?string
    {
        return static::getModel()::count();
    }


    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Group::make()->schema([
                    Section::make()->schema([
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
                    Section::make('Pricing & Inventory')->schema([
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
                    ])->columns(2),
                ]),
                Group::make()->schema([
                    Section::make('status')->schema([
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
                    ]),
                    Section::make('image')
                        ->schema([
                            FileUpload::make('image')
                                ->image()
                                ->imageEditor()
                                ->nullable()
                                ->preserveFilenames()
                                ->directory('form-attachment')
                        ])->collapsible(),
                    Section::make('associations')->schema([
                        Select::make('brand_id')
                            ->relationship('brand', 'name')
                            ->required(),
                        Select::make('categories')
                            ->relationship('categories', 'name')
                            ->multiple()
                            ->required(),
                    ])
                ])
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
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
                TernaryFilter::make('is_visable')
                    ->label('is visiable')
                    ->boolean()
                    ->trueLabel('only visiable product')
                    ->falseLabel('only hidden product')
                    ->native(false),


                SelectFilter::make('brand')
                    ->relationship('brand', 'name'),
            ])

            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => Pages\ListProducts::route('/'),
            'create' => Pages\CreateProduct::route('/create'),
            'edit' => Pages\EditProduct::route('/{record}/edit'),
        ];
    }
}
