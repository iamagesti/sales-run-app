<?php

namespace App\Filament\Resources;

use Filament\Forms;
use App\Models\User;
use Filament\Tables;
use App\Models\Product;
use App\Models\Category;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Forms\Components\Image;
use Forms\Components\Images;
use Illuminate\Support\Carbon;
use Filament\Resources\Resource;
use Illuminate\Support\HtmlString;
use Filament\Tables\Filters\Filter;
use Illuminate\Support\Facades\Auth;
use Filament\Notifications\Notification;
use Illuminate\Notifications\Notifiable;
use Filament\Forms\Components\DatePicker;
use Filament\Tables\Filters\SelectFilter;
use Illuminate\Database\Eloquent\Builder;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DateTimePicker;
use App\Filament\Resources\ProductResource\Pages;
use Illuminate\Database\Eloquent\SoftDeletingScope;
use App\Filament\Resources\ProductResource\RelationManagers;

class ProductResource extends Resource
{
    protected static ?string $model = Product::class;

    protected static ?string $navigationIcon = 'heroicon-o-cube';

    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Forms\Components\TextInput::make('barcode')
                    ->maxLength(255)
                    ->required()
                    ->unique(ignoreRecord:true),
                Forms\Components\TextInput::make('name')
                    ->required()
                    ->maxLength(255),
                Forms\Components\Select::make('category_id')
                    ->required()
                    ->searchable()
                    ->preload()
                    ->relationship('category', 'name'),
                Forms\Components\FileUpload::make('image')
                    ->image()
                    ->maxSize(1024)
                    ->directory('products')
                    ->removeUploadedFileButtonPosition('right')
                    ->visibility('public'),
                Forms\Components\TextInput::make('selling_price')
                    ->label("Price")
                    ->numeric()
                    ->required()
                    ->prefix('IDR'),
                Forms\Components\TextInput::make('stock')
                    ->numeric()
                    ->required()
                    ->default(1)
                    ->rule('min:1'),
                Forms\Components\DatePicker::make('expired_at')
                    ->required(),
                    //->format('d/m/Y'),
                Forms\Components\Toggle::make('is_active')
                    ->label('Status')
                    ->required()
                    ->default(true),
                Forms\Components\MarkdownEditor::make('description')
                    ->columnSpanFull()
                    ->fileAttachmentsDirectory('products'),
            ]);
    }

    public static function table(Table $table): Table
    {
        self::checkLowStockNotification();
        self::checkExpiredProductNotification();
        return $table
            ->columns([
                Tables\Columns\ImageColumn::make('image')
                    ->label('Image'),
                Tables\Columns\TextColumn::make('barcode')
                    ->searchable(),
                Tables\Columns\TextColumn::make('name')
                    ->searchable(),
                Tables\Columns\TextColumn::make('category.name')
                    ->sortable(),
                Tables\Columns\TextColumn::make('selling_price')
                    ->label('Price')
                    ->money('IDR')
                    ->sortable(),
                Tables\Columns\TextColumn::make('stock')
                    ->sortable()
                    ->color(static function ($state): string {
                        if ($state < 10) {
                            return 'danger';
                        } elseif ($state < 20) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    }),
                Tables\Columns\TextColumn::make('expired_at')
                    ->label('Expired Date')
                    ->dateTime()
                    ->sortable()
                    ->color(static function ($state): string {
                        $today = Carbon::today();
                        $expirationDate = Carbon::parse($state);


                        $daysLeft = $today->diffInDays($expirationDate);

                        if ($daysLeft <= 7) {
                            return 'danger';
                        } elseif ($daysLeft <= 30) {
                            return 'warning';
                        } else {
                            return 'success';
                        }
                    }),
                Tables\Columns\IconColumn::make('is_active')
                    ->label('Status')
                    ->boolean(),
                Tables\Columns\TextColumn::make('description'),
            ])
            ->filters([
                // SelectFilter::make('category')
                // ->relationship('category','name')
                SelectFilter::make('is_active')
                    ->label('Status')
                    ->options([
                        true => 'Available',
                        false => 'Unavailable',
                    ]),
                SelectFilter::make('category')
                    ->options(function () {
                        return Category::withCount('products')
                            ->get()
                            ->mapWithKeys(function ($category) {
                                return [
                                    $category->id => new HtmlString($category->name . " <span class='text-gray-500'>({$category->products_count})</span>")
                                ];
                            })
                            ->toArray();
                    })
                    ->query(function (Builder $query, array $data): Builder {
                        // Check if 'category' key exists in the array
                        return $query->when(
                            isset($data['category']), // Ensure the key exists
                            fn(Builder $query) => $query->where('category_id', $data['category'])
                        );
                    }),
                Filter::make('expired_at')
                    ->form([
                        Forms\Components\DatePicker::make('Expired From'),
                        Forms\Components\DatePicker::make('Expired Until'),
                    ])
                    ->query(function (Builder $query, array $data): Builder {
                        return $query
                            ->when(
                                $data['Expired From'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expired_at', '>=', $date),
                            )
                            ->when(
                                $data['Expired Until'],
                                fn(Builder $query, $date): Builder => $query->whereDate('expired_at', '<=', $date),
                            );
                    })
                    ->indicateUsing(function (array $data): array {
                        $indicators = [];

                        if ($data['Expired From'] ?? null) {
                            $indicators['from'] = 'Expired from ' . Carbon::parse($data['Expired From'])->toFormattedDateString();
                        }

                        if ($data['Expired Until'] ?? null) {
                            $indicators['until'] = 'Expired until ' . Carbon::parse($data['Expired Until'])->toFormattedDateString();
                        }

                        return $indicators;
                    })

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

    protected static function checkLowStockNotification(): void
    {
        $recipients = Auth::user();
        $lowStockProducts = Product::where('stock', '<', 10)->get();

        if ($lowStockProducts->isNotEmpty()) {
            // Generate a notification message listing all products with low stock
            $productNames = $lowStockProducts->pluck('name')->join(', ');

            Notification::make()
                ->title('Low Stock Alert')
                ->body("The following products have low stock: $productNames. Please restock ASAP.")
                ->danger()
                ->sendToDatabase($recipients);
        }
    }

    protected static function checkExpiredProductNotification(): void
    {
        // Ambil produk yang kedaluwarsa hari ini atau besok
        $expiredToday = Product::whereDate('expired_at', Carbon::today())->get();
        $expiredTomorrow = Product::whereDate('expired_at', Carbon::tomorrow())->get();

        if ($expiredToday->isNotEmpty() || $expiredTomorrow->isNotEmpty()) {
            $recipients = Auth::user();

            $productNamesToday = $expiredToday->pluck('name')->join(', ');
            $productNamesTomorrow = $expiredTomorrow->pluck('name')->join(', ');

            $notificationBody = '';

            if ($productNamesToday) {
                $notificationBody .= "The following products expire today: $productNamesToday. ";
            }

            if ($productNamesTomorrow) {
                $notificationBody .= "The following products expire tomorrow: $productNamesTomorrow. ";
            }

            Notification::make()
                ->title('Product Expiration Alert')
                ->body($notificationBody . "Please check the inventory and remove the products.")
                ->danger()
                ->sendToDatabase($recipients);
        }
    }

}
