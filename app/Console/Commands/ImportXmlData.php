<?php

namespace App\Console\Commands;

use App\Models\Guest;
use App\Models\Hotel;
use App\Models\Payment;
use App\Models\PaymentMethod;
use App\Models\Reserve;
use App\Models\ReserveDaily;
use App\Models\Room;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class ImportXmlData extends Command
{
    protected $signature = 'xml:import';

    protected $description = 'Importa dados de hotelaria a partir dos arquivos XML';

    public function handle(): int
    {
        Log::info('Importação XML iniciada');

        if ($this->importHotels() === Command::FAILURE) {
            return Command::FAILURE;
        }

        if ($this->importRooms() === Command::FAILURE) {
            return Command::FAILURE;
        }

        if ($this->importReserves() === Command::FAILURE) {
            return Command::FAILURE;
        }

        return Command::SUCCESS;

        Log::info('Importação XML concluída com sucesso');
    }

    private function importHotels(): int
    {
        $filePath = base_path('docs/xml/hotels.xml');

        if (!file_exists($filePath)) {
            $this->error("Arquivo XML não encontrado: {$filePath}");

            return Command::FAILURE;
        }

        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $this->error('Não foi possível ler o arquivo XML.');

            return Command::FAILURE;
        }

        $importedHotels = 0;

        foreach ($xml->Hotel as $hotelXml) {
            $externalId = (int) $hotelXml['id'];
            $name = trim((string) $hotelXml->Name);

            Hotel::updateOrCreate(
                ['external_id' => $externalId],
                ['name' => $name],
            );

            $importedHotels++;
        }

        $this->info(
            "Importação concluída: {$importedHotels} hotel(is) processado(s)."
        );

        Log::info('Importação de hotéis concluída', [
            'imported_hotels' => $importedHotels,
        ]);

        return Command::SUCCESS;
    }

    private function importRooms(): int
    {
        $filePath = base_path('docs/xml/rooms.xml');

        if (!file_exists($filePath)) {
            $this->error("Arquivo XML não encontrado: {$filePath}");

            return Command::FAILURE;
        }

        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $this->error('Não foi possível ler o arquivo XML.');

            return Command::FAILURE;
        }

        $importedRooms = 0;

        foreach ($xml->Room as $roomXml) {
            $externalId = (int) $roomXml['id'];
            $externalHotelId = (int) $roomXml['hotelCode'];
            $name = trim((string) $roomXml->Name);

            $hotel = Hotel::where(
                'external_id',
                $externalHotelId
            )->first();

            if ($hotel === null) {
                $this->error(
                    "O hotel {$externalHotelId} não existe no banco de dados."
                );

                return Command::FAILURE;
            }

            Room::updateOrCreate(
                [
                    'hotel_id' => $hotel->id,
                    'external_id' => $externalId,
                ],
                [
                    'name' => $name,
                ],
            );

            $importedRooms++;
        }

        $this->info(
            "Importação concluída: {$importedRooms} quarto(s) processado(s)."
        );

        Log::info('Importação de quartos concluída', [
            'imported_rooms' => $importedRooms,
        ]);

        return Command::SUCCESS;
    }

    private function importReserves(): int
    {
        $filePath = base_path('docs/xml/reserves.xml');

        if (!file_exists($filePath)) {
            $this->error("Arquivo XML não encontrado: {$filePath}");

            return Command::FAILURE;
        }

        $xml = simplexml_load_file($filePath);

        if ($xml === false) {
            $this->error('Não foi possível ler o arquivo XML.');

            return Command::FAILURE;
        }

        $importedReserves = 0;

        foreach ($xml->Reserve as $reserveXml) {
            $externalId = (int) $reserveXml['id'];
            $externalHotelId = (int) $reserveXml['hotelCode'];
            $externalRoomId = (int) $reserveXml['roomCode'];
            $checkIn = trim((string) $reserveXml->CheckIn);
            $checkOut = trim((string) $reserveXml->CheckOut);
            $total = trim((string) $reserveXml->Total);

            $hotel = Hotel::where(
                'external_id',
                $externalHotelId
            )->first();

            if ($hotel === null) {
                $this->error(
                    "O hotel {$externalHotelId} não existe no banco de dados."
                );

                return Command::FAILURE;
            }

            $room = Room::where(
                'external_id',
                $externalRoomId
            )
                ->where('hotel_id', $hotel->id)
                ->first();

            if ($room === null) {
                $this->error(
                    "O quarto {$externalRoomId} não pertence ao hotel "
                    . "{$externalHotelId} ou não existe."
                );

                return Command::FAILURE;
            }

            if (!isset($reserveXml->Guests->Guest[0])) {
                $this->error(
                    "A reserva {$externalId} não possui hóspede."
                );

                return Command::FAILURE;
            }

            $guestXml = $reserveXml->Guests->Guest[0];

            $guestName = trim((string) $guestXml->Name);
            $guestLastName = trim((string) $guestXml->LastName);
            $guestPhone = trim((string) $guestXml->Phone);

            if ($guestPhone === '') {
                $this->error(
                    "A reserva {$externalId} possui hóspede sem telefone."
                );

                return Command::FAILURE;
            }

            try {
                DB::transaction(function () use (
                    $externalId,
                    $checkIn,
                    $checkOut,
                    $total,
                    $guestName,
                    $guestLastName,
                    $guestPhone,
                    $hotel,
                    $room,
                    $reserveXml
                ) {
                    $guest = Guest::updateOrCreate(
                        ['phone' => $guestPhone],
                        [
                            'name' => $guestName,
                            'last_name' => $guestLastName,
                        ],
                    );

                    $reserve = Reserve::updateOrCreate(
                        ['external_id' => $externalId],
                        [
                            'hotel_id' => $hotel->id,
                            'room_id' => $room->id,
                            'guest_id' => $guest->id,
                            'check_in' => $checkIn,
                            'check_out' => $checkOut,
                            'total' => $total,
                        ],
                    );

                    foreach ($reserveXml->Dailies->Daily as $dailyXml) {
                        $date = trim((string) $dailyXml->Date);
                        $value = trim((string) $dailyXml->Value);

                        ReserveDaily::updateOrCreate(
                            [
                                'reserve_id' => $reserve->id,
                                'date' => $date,
                            ],
                            [
                                'value' => $value,
                            ],
                        );
                    }

                    if (isset($reserveXml->Payments->Payment)) {
                        foreach (
                            $reserveXml->Payments->Payment as $paymentXml
                        ) {
                            $method = trim(
                                (string) $paymentXml->Method
                            );

                            $value = trim(
                                (string) $paymentXml->Value
                            );

                            $paymentMethod = PaymentMethod::firstOrCreate([
                                'external_id' => $method,
                            ]);

                            if ($paymentMethod === null) {
                                throw new \RuntimeException(
                                    "Método de pagamento não encontrado: "
                                    . "{$method}"
                                );
                            }

                            Payment::updateOrCreate(
                                [
                                    'reserve_id' => $reserve->id,
                                    'payment_method_id' => $paymentMethod->id,
                                    'value' => $value,
                                ],
                                [],
                            );
                        }
                    }
                });
            } catch (\Throwable $e) {

                Log::error('Falha ao importar reserva XML', [
                    'external_id' => $externalId,
                    'message' => $e->getMessage(),
                ]);

                $this->error(
                    "Erro ao importar a reserva {$externalId}: "
                    . $e->getMessage()
                );

                return Command::FAILURE;
            }

            $importedReserves++;
        }

        $this->info(
            "Importação concluída: {$importedReserves} reserva(s) "
            . "processada(s)."
        );

        Log::info('Importação de reservas concluída', [
            'imported_reserves' => $importedReserves,
        ]);

        return Command::SUCCESS;
    }
}
