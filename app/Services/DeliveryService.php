<?php
// app/Services/DeliveryService.php
namespace App\Services;

class DeliveryService
{
    public static function getLocations()
    {
        return [
            // Pickup at Cafeteria (FREE)
            'pickup_cafeteria' => [
                'name' => 'Pickup at Cafeteria Counter',
                'type' => 'pickup',
                'fee' => 0,
                'instructions' => 'Collect your order at the cafeteria counter',
                'available_times' => '7:00 AM - 6:00 PM'
            ],

            // Pickup at Umbrellas (FREE)
            'pickup_umbrella' => [
                'name' => 'Pickup at Cafeteria Umbrellas',
                'type' => 'pickup',
                'fee' => 0,
                'instructions' => 'Wait at the umbrellas outside the cafeteria',
                'available_times' => '7:00 AM - 6:00 PM'
            ],

            // Delivery to KTVTC Offices (FREE)
            'delivery_staff_room' => [
                'name' => 'KTVTC Staff Room',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the staff room',
                'available_times' => '8:00 AM - 5:00 PM'
            ],
            'delivery_principal' => [
                'name' => 'Principal Office',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the Principal\'s office',
                'available_times' => '8:00 AM - 5:00 PM'
            ],
            'delivery_gm' => [
                'name' => 'GM Office',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the GM\'s office',
                'available_times' => '8:00 AM - 5:00 PM'
            ],
            'delivery_admin' => [
                'name' => 'Admin Office',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the Admin office',
                'available_times' => '8:00 AM - 5:00 PM'
            ],

            // Delivery to Kenswed Facilities (FREE)
            'delivery_hospital' => [
                'name' => 'Kenswed Hospital',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the hospital reception',
                'available_times' => '9:00 AM - 4:00 PM'
            ],
            'delivery_dental' => [
                'name' => 'Kenswed Dental Clinic',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the dental clinic',
                'available_times' => '9:00 AM - 4:00 PM'
            ],
            'delivery_high_school' => [
                'name' => 'Kenswed High School',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the high school office',
                'available_times' => '8:00 AM - 4:00 PM'
            ],
            'delivery_ngong_town' => [
                'name' => 'KTVTC Ngong Town Campus',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'We\'ll deliver to the Ngong Town campus office',
                'available_times' => '9:00 AM - 3:00 PM'
            ],

            // Other (FREE)
            'other_specified' => [
                'name' => 'Other Location (Specify)',
                'type' => 'delivery',
                'fee' => 0,
                'instructions' => 'Enter specific location details',
                'available_times' => '8:00 AM - 4:00 PM'
            ]
        ];
    }

    public static function getLocation($locationId)
    {
        $locations = self::getLocations();
        return $locations[$locationId] ?? null;
    }

    public static function getPickupLocations()
    {
        return array_filter(self::getLocations(), function($location) {
            return $location['type'] === 'pickup';
        });
    }

    public static function getDeliveryLocations()
    {
        return array_filter(self::getLocations(), function($location) {
            return $location['type'] === 'delivery';
        });
    }
}
