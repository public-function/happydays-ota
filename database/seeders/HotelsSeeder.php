<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Hotel;
use App\Models\RoomType;
use App\Models\HotelRoomType;

class HotelsSeeder extends Seeder
{
    /**
     * Run the database seeders.
     */
    public function run(): void
    {
        $hotels = [
            [
                'name' => 'Hotel Skagen',
                'address' => 'Vestre Strandvej 35',
                'city' => 'Skagen',
                'country' => 'Denmark',
                'postal_code' => '9990',
                'phone' => '+45 9844 1234',
                'email' => 'info@hotelskagen.dk',
                'website' => 'https://hotelskagen.dk',
                'status' => 'active',
                'checkin_time' => '15:00:00',
                'checkout_time' => '11:00:00',
                'timezone' => 'Europe/Copenhagen',
            ],
            [
                'name' => 'Hotel Copenhagen',
                'address' => 'Kongens Nytorv 15',
                'city' => 'Copenhagen',
                'country' => 'Denmark',
                'postal_code' => '1250',
                'phone' => '+45 3311 2233',
                'email' => 'booking@hotelcopenhagen.dk',
                'website' => 'https://hotelcopenhagen.dk',
                'status' => 'active',
                'checkin_time' => '15:00:00',
                'checkout_time' => '12:00:00',
                'timezone' => 'Europe/Copenhagen',
            ],
            [
                'name' => 'Hotel Aarhus',
                'address' => 'Banegårdspladsen 12',
                'city' => 'Aarhus',
                'country' => 'Denmark',
                'postal_code' => '8000',
                'phone' => '+45 8620 3344',
                'email' => 'info@hotelaarhus.dk',
                'website' => 'https://hotelaarhus.dk',
                'status' => 'active',
                'checkin_time' => '15:00:00',
                'checkout_time' => '11:00:00',
                'timezone' => 'Europe/Copenhagen',
            ],
            [
                'name' => 'Hotel Aalborg',
                'address' => 'Jomfru Ane Gade 21',
                'city' => 'Aalborg',
                'country' => 'Denmark',
                'postal_code' => '9000',
                'phone' => '+45 9812 4455',
                'email' => 'welcome@hotelaalborg.dk',
                'website' => 'https://hotelaalborg.dk',
                'status' => 'active',
                'checkin_time' => '15:00:00',
                'checkout_time' => '11:00:00',
                'timezone' => 'Europe/Copenhagen',
            ],
            [
                'name' => 'Hotel Odense',
                'address' => 'Vestergade 48',
                'city' => 'Odense',
                'country' => 'Denmark',
                'postal_code' => '5000',
                'phone' => '+45 6611 5566',
                'email' => 'booking@hotelodense.dk',
                'website' => 'https://hotelodense.dk',
                'status' => 'active',
                'checkin_time' => '15:00:00',
                'checkout_time' => '11:00:00',
                'timezone' => 'Europe/Copenhagen',
            ],
        ];

        $hotelRoomTypeMappings = [
            'Skagen' => [
                ['supplier_code' => 'ENK', 'supplier_name' => 'Single Economy', 'min_occupancy' => 1, 'max_occupancy' => 1],
                ['supplier_code' => 'DBL2', 'supplier_name' => 'Double Standard', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'TWN', 'supplier_name' => 'Twin Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'FAM', 'supplier_name' => 'Family Room', 'min_occupancy' => 2, 'max_occupancy' => 4],
                ['supplier_code' => 'STE', 'supplier_name' => 'Suite', 'min_occupancy' => 1, 'max_occupancy' => 4],
            ],
            'Copenhagen' => [
                ['supplier_code' => 'ENK', 'supplier_name' => 'Single Room', 'min_occupancy' => 1, 'max_occupancy' => 1],
                ['supplier_code' => 'DBL2', 'supplier_name' => 'Double Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'DBL4', 'supplier_name' => 'Double Premium', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'TWN', 'supplier_name' => 'Twin Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'STE', 'supplier_name' => 'Executive Suite', 'min_occupancy' => 1, 'max_occupancy' => 4],
            ],
            'Aarhus' => [
                ['supplier_code' => 'ENK', 'supplier_name' => 'Single Room', 'min_occupancy' => 1, 'max_occupancy' => 1],
                ['supplier_code' => 'DBL2', 'supplier_name' => 'Standard Double', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'TWN', 'supplier_name' => 'Twin Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'FAM', 'supplier_name' => 'Family Room', 'min_occupancy' => 2, 'max_occupancy' => 4],
            ],
            'Aalborg' => [
                ['supplier_code' => 'ENK', 'supplier_name' => 'Single Room', 'min_occupancy' => 1, 'max_occupancy' => 1],
                ['supplier_code' => 'DBL2', 'supplier_name' => 'Double Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'TWN', 'supplier_name' => 'Twin Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'STE', 'supplier_name' => 'Junior Suite', 'min_occupancy' => 1, 'max_occupancy' => 3],
            ],
            'Odense' => [
                ['supplier_code' => 'ENK', 'supplier_name' => 'Single Room', 'min_occupancy' => 1, 'max_occupancy' => 1],
                ['supplier_code' => 'DBL2', 'supplier_name' => 'Double Room', 'min_occupancy' => 1, 'max_occupancy' => 2],
                ['supplier_code' => 'FAM', 'supplier_name' => 'Family Room', 'min_occupancy' => 2, 'max_occupancy' => 4],
                ['supplier_code' => 'STE', 'supplier_name' => 'Suite', 'min_occupancy' => 1, 'max_occupancy' => 4],
            ],
        ];

        foreach ($hotels as $hotelData) {
            $hotel = Hotel::updateOrCreate(
                ['name' => $hotelData['name']],
                $hotelData
            );

            $roomTypes = $hotelRoomTypeMappings[$hotel->city] ?? $hotelRoomTypeMappings['Copenhagen'];

            foreach ($roomTypes as $roomType) {
                HotelRoomType::updateOrCreate(
                    ['hotel_id' => $hotel->id, 'supplier_code' => $roomType['supplier_code']],
                    array_merge($roomType, ['status' => 'active'])
                );
            }
        }

        $this->command->info('Hotels and room types seeded successfully.');
    }
}
