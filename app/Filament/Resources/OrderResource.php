<?php

namespace App\Filament\Resources;

use App\Enums\OrderStatusEnum;
use App\Filament\Resources\OrderResource\Pages;
use App\Filament\Resources\OrderResource\RelationManagers;
use App\Models\Order;
use App\Models\Product;
use Filament\Forms;
use Filament\Forms\Components\MarkdownEditor;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class OrderResource extends Resource
{
    protected static ?string $model = Order::class;

    protected static ?string $navigationIcon = 'heroicon-o-rectangle-stack';
    protected static ?int $navigationSort = 3;
    protected static ?string $navigationGroup = 'shop';

    public static function getNavigationBadge(): ?string
    {
        return static::getmodel()::where('status', '=', 'processing')->count();
    }
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\Wizard::make([
                    Forms\Components\Wizard\Step::make('order details')
                        ->schema([
                            Forms\Components\TextInput::make('number')
                                ->default('OR-' . random_int(10000, 99999))
                                ->disabled()
                                ->required()
                                ->dehydrated(),

                            Forms\Components\Select::make('customer_id')
                                ->relationship('customer', 'name')
                                ->searchable()
                                ->required(),
                            TextInput::make('shipping_price')
                                ->dehydrated()
                                ->label('Shipping costs')
                                ->numeric()
                                ->required(),
                            Forms\Components\Select::make('type')
                                ->options([
                                    'pending' => OrderStatusEnum::PENDING->value,
                                    'processing' => OrderStatusEnum::PROCESSING->value,
                                    'Completed' => OrderStatusEnum::COMPLETED->value,
                                    'Declined' => OrderStatusEnum::DECLINED->value,
                                ])
                                ->required(),
                            Forms\Components\MarkdownEditor::make('notes')
                                ->columnSpanFull()
                        ])->Columns(2),
                    Forms\Components\Wizard\Step::make('order items')
                        ->schema([
                            Repeater::make('items')
                                ->relationship()
                                ->schema([
                                    Select::make('product_id')
                                        ->label('Product')
                                        ->options(Product::query()->pluck('name', 'id'))
                                        ->required()
                                        ->reactive()
                                        ->afterStateUpdated(fn ($state, Forms\Set $set) =>
                                        $set('unit_price', Product::find($state)?->price ?? 0)),
                                    TextInput::make('quantity')
                                        ->numeric()
                                        ->default(1)
                                        ->required()
                                        ->live()
                                        ->dehydrated(),
                                    TextInput::make('unit_price')
                                        ->label('unit price')
                                        ->disabled()
                                        ->dehydrated()
                                        ->numeric()
                                        ->required(),
                                    Forms\Components\Placeholder::make('total_price')
                                        ->label('totale price')
                                        ->content(function ($get) {
                                            return $get('quantity') * $get('unit_price');
                                        })
                                ])->columns(4)
                        ])
                ])->columnSpanFull()
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('number')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('customer.name')
                    ->searchable()
                    ->sortable()
                    ->toggleable(),
                TextColumn::make('status')
                    ->searchable()
                    ->sortable(),
                TextColumn::make('created_at')
                    ->label('Order date')
                    ->date(),
            ])
            ->filters([
                //
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
            'index' => Pages\ListOrders::route('/'),
            'create' => Pages\CreateOrder::route('/create'),
            'edit' => Pages\EditOrder::route('/{record}/edit'),
        ];
    }
}