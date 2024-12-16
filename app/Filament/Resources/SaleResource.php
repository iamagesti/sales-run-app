<?php

namespace App\Filament\Resources;

use id;
use Filament\Forms;
use App\Models\Sale;
use App\Models\User;
use Filament\Tables;
use App\Models\Product;
use Filament\Forms\Get;
use Filament\Forms\Set;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Illuminate\Support\Carbon;
use Illuminate\Support\Number;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Actions\Action;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Forms\Components\Hidden;
use Filament\Forms\Components\Select;
use App\Filament\Exports\SaleExporter;
use Filament\Forms\Components\Actions;
use Filament\Forms\Components\Section;
//use Illuminate\Support\Set;
use Filament\Forms\Components\Repeater;
use Filament\Forms\Components\TextInput;
use Filament\Notifications\Notification;
use Filament\Tables\Enums\FiltersLayout;
use Filament\Tables\Actions\ExportAction;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\Placeholder;
use App\Filament\Resources\ProductResource;
use Filament\Forms\Components\ToggleButtons;
use Filament\Tables\Columns\Summarizers\Sum;
use Filament\Tables\Actions\ExportBulkAction;
use App\Filament\Resources\SaleResource\Pages;
use Filament\Tables\Columns\Summarizers\Average;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\SaleResource\RelationManagers;
use Purifier;
use App\Filament\Resources\Purify;




class SaleResource extends Resource
{
    protected static ?string $model = Sale::class;

    protected static ?string $navigationIcon = 'heroicon-o-banknotes';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make('Customer Details')->schema([
                    Forms\Components\TextInput::make('customer_name')
                        ->label('Customer Name')
                        ->maxLength(255),
                ]),
                Section::make('Sale Items')->schema([
                    Repeater::make('items')
                        ->relationship()
                        ->schema([
                            Forms\Components\Select::make('product_id')
                                ->required()
                                ->allowHtml()
                                ->searchable(['barcode', 'name'])
                                ->searchPrompt('Search products by name or barcode')
                                ->noSearchResultsMessage('No products found.')
                                ->preload()
                                ->distinct()
                                ->live()
                                ->relationship('product',
                                modifyQueryUsing: fn (Builder $query) => $query ->orderBy('image') ->orderBy('barcode')->orderBy('name'),)
                                ->getOptionLabelFromRecordUsing(function (Product $record): string {
                                    return self::getCleanOptionString($record);
                                })
                                ->disableOptionsWhenSelectedInSiblingRepeaterItems()
                                ->columnSpan(4)
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $set('unit_amount', Product::find($state)?->selling_price ?? 0);
                                })
                                ->afterStateUpdated(function ($state, Set $set) {
                                    $set('total_amount', Product::find($state)?->selling_price ?? 0);
                                }),
                            Forms\Components\TextInput::make('quantity')
                                ->required()
                                ->numeric()
                                ->default(1)
                                ->minValue(1)
                                ->columnSpan(2)
                                ->reactive()
                                ->afterStateUpdated(function ($state, Set $set, Get $get) {
                                    $product = Product::find($get('product_id'));
                                    if ($product && $state > $product->stock) {
                                        Notification::make()
                                            ->title('Insufficient stock')
                                            ->body('The requested quantity exceeds the available stock')
                                            ->warning()
                                            ->send();

                                        $set('quantity', $product->stock);
                                        $state = $product->stock;
                                    }
                                    $set('total_amount', $state * $get('unit_amount'));
                                }),
                            Forms\Components\TextInput::make('unit_amount')
                                ->numeric()
                                ->required()
                                //->disabled()
                                ->readOnly()
                                ->prefix('IDR')
                                ->dehydrated()
                                ->columnSpan(3),
                            Forms\Components\TextInput::make('total_amount')
                                ->numeric()
                                ->required()
                                ->prefix('IDR')
                                ->dehydrated()
                                ->columnSpan(3)
                                ->readonly(),

                        ])->columns(12),

                    Placeholder::make('sub_total_placeholder')
                        ->label('Subtotal')
                        ->content(function (Get $get, Set $set) {
                            $total = 0;
                            if (!$repeaters = $get('items')) {
                                return $total;
                            }
                            foreach ($repeaters as $key => $repeater) {
                                $total += $get('items.' . $key . '.total_amount');
                            }
                            $set('sub_total', $total);
                            return Number::currency($total, 'IDR');
                        }),

                    Hidden::make('sub_total')
                        ->default(0),
                ])->columnSpanFull(),

                Section::make('Discount and Tax')->schema([
                    Forms\Components\TextInput::make('discount_percentage')
                        ->label('Discount Percentage (%)')
                        ->numeric()
                        ->reactive()
                        ->default(0)
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $discountPercentage = $get('discount_percentage') ?? 0;
                            $total = $get('sub_total') ?? 0;
                            $discountAmount = ($discountPercentage / 100) * $total;
                            $set('discount_amount', $discountAmount);
                        })
                        ->columnSpan(6),

                    Forms\Components\TextInput::make('discount_amount')
                        ->label('Discount Amount')
                        ->numeric()
                        ->prefix('IDR')
                        ->dehydrated()
                        ->readOnly()
                        ->columnSpan(6)
                        ->default(0),

                    Forms\Components\TextInput::make('tax_percentage')
                        ->label('Tax Percentage (%)')
                        ->numeric()
                        ->reactive()
                        ->afterStateUpdated(function (Set $set, Get $get) {
                            $taxPercentage = $get('tax_percentage') ?? 0;
                            $subTotal = $get('sub_total') ?? 0;
                            $taxAmount = ($taxPercentage / 100) * $subTotal;
                            $set('tax_amount', $taxAmount);
                        })
                        ->columnSpan(6)
                        ->default(0),

                    Forms\Components\TextInput::make('tax_amount')
                        ->label('Tax Amount')
                        ->numeric()
                        //->disabled()
                        ->prefix('IDR')
                        ->dehydrated()
                        ->readOnly()
                        ->columnSpan(6)
                        ->default(0),


                    Placeholder::make('grand_total_placeholder')
                        ->label('Grand Total')
                        ->content(function (Get $get, Set $set) {
                            $subTotal = $get('sub_total') ?? 0;
                            $discountAmount = $get('discount_amount') ?? 0;
                            $taxAmount = $get('tax_amount') ?? 0;
                            $grandTotal = $subTotal - $discountAmount + $taxAmount;
                            $set('grand_total', $grandTotal);
                            return Number::currency($grandTotal, 'IDR');
                        })
                        ->columnSpanFull(),
                    Hidden::make('grand_total')
                        ->default(0),
                ])->columns(12),
                Section::make('Payment Details')->schema([
                    Forms\Components\Select::make('payment_method')
                        ->options([
                            'cash' => 'Cash',
                            'QRIS' => 'QRIS',
                            'transfer' => 'Transfer',
                        ])
                        ->default('cash')
                        ->required()
                        ->columnSpan(4),
                    Forms\Components\TextInput::make('paid_amount')
                        ->numeric()
                        ->prefix('IDR')
                        ->reactive()
                        ->label('Paid Amount')
                        ->afterStateUpdated(function (Set $set, Get $get, $state) {
                            self::updateExchangePaid($get, $set);
                        })
                        ->columnSpan(4),
                    Forms\Components\TextInput::make('change_amount')
                        ->numeric()
                        ->prefix('IDR')
                        ->label('Change Amount')
                        ->readOnly()
                        ->columnSpan(4),
                ])->columns(12),
                Section::make('Notes')->schema([
                    Forms\Components\TextInput::make('notes')
                        ->label('Notes')
                        ->maxLength(255),
                ]),

                Section::make('Cashier Details')->schema([
                    Forms\Components\TextInput::make('cashier_name')
                        ->label('Cashier')
                        ->default(Auth::user()->name)
                        ->readOnly()
                        ->afterStateHydrated(function (Forms\Components\TextInput $component, $state) {
                            $component->state(Auth::user()->name);
                        }),
                    Forms\Components\Hidden::make('user_id')
                        ->default(Auth::user()->id)
                ]),




            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('created_at')
                    ->label('Transaction Date')
                    ->dateTime()
                    ->sortable(),
                Tables\Columns\TextColumn::make('items.product.name')
                    ->label('Products'),
                Tables\Columns\TextColumn::make('grand_total')
                    ->numeric()
                    ->money('IDR')
                    ->sortable()
                    ->summarize([
                        Sum::make()->money('IDR'),
                    ]),
                Tables\Columns\TextColumn::make('payment_method')
                    ->searchable(),
                Tables\Columns\TextColumn::make('notes'),
                Tables\Columns\TextColumn::make('user.name')
                    ->label('Cashier')
                    ->searchable(),
                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Filter::make('created_at')
                    ->form([
                        Forms\Components\DatePicker::make('Transaction From'),
                        Forms\Components\DatePicker::make('Transaction Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Transaction From'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '>=', $date),
                            )
                            ->when(
                                $data['Transaction Until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('created_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['Transaction From'] ?? null) {
                            $indicators['from'] = 'Created from ' . Carbon::parse($data['Transaction From'])->toFormattedDateString();
                        }

                        if ($data['Transaction Until'] ?? null) {
                            $indicators['until'] = 'Created until ' . Carbon::parse($data['Transaction Until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })
            ])
            ->actions([
                Tables\Actions\ActionGroup::make([
                    Tables\Actions\ViewAction::make(),
                    Tables\Actions\EditAction::make(),
                    Tables\Actions\DeleteAction::make(),
                    Action::make('View Invoice')
                    ->label('Preview Invoice')
                    ->url(fn (Sale $record): string => route('preview-invoice', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-s-document'),
                    Action::make('Download Invoice')
                    ->label('Download Invoice')
                    ->url(fn (Sale $record): string => route('download-invoice', $record))
                    ->openUrlInNewTab()
                    ->icon('heroicon-s-arrow-down-tray'),
                ])
            ])
            ->headerActions([
                ExportAction::make()->exporter(SaleExporter::class)
            ])
            ->bulkActions([
                Tables\Actions\BulkActionGroup::make([
                    Tables\Actions\DeleteBulkAction::make(),
                ]),
                ExportBulkAction::make()->exporter(SaleExporter::class),
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
            'index' => Pages\ListSales::route('/'),
            'create' => Pages\CreateSale::route('/create'),
            'view' => Pages\ViewSale::route('/{record}'),
            'edit' => Pages\EditSale::route('/{record}/edit'),
        ];
    }
    protected static function updateExchangePaid(Forms\Get $get, Forms\Set $set): void
    {
        $paidAmount = (int) $get('paid_amount') ?? 0;
        $grandTotal = (int) $get('grand_total') ?? 0;
        $exchangePaid = $paidAmount - $grandTotal;
        $set('change_amount', $exchangePaid);
    }

    public static function getCleanOptionString(Product $product): string
{
    return (
        view('filament.components.select-product-result')
            ->with('image', $product->image)
            ->with('barcode', $product->barcode)
            ->with('name', $product->name)
            ->render()
    );
}
}
