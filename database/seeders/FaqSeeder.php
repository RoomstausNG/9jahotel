<?php

namespace Database\Seeders;

use App\Models\Faq;
use App\Models\Hotel;
use App\Models\Question;
use Illuminate\Database\Seeder;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;

class FaqSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $hotels = Hotel::all();

        // Sample answers for FAQs
        $sampleAnswers = [
            'Check-in is at 2:00 PM and check-out is at 12:00 PM.',
            'Yes, we offer shuttle service for an additional fee. Please contact the front desk to arrange.',
            'Yes, breakfast is included in all room rates.',
            'Yes, we provide complimentary high-speed WiFi throughout the property.',
            'Yes, we have an outdoor swimming pool open from 7:00 AM to 10:00 PM.',
            'Our fitness center is open 24/7 for hotel guests.',
            'We are pet-friendly. Additional charges may apply.',
            'Room service is available from 6:00 AM to 11:00 PM daily.',
            'We offer both self-parking and valet parking options.',
            'All rooms include air conditioning, flat-screen TV, mini-fridge, coffee maker, and premium bedding.'
        ];

        // For each hotel, create 5-8 random FAQs
        foreach ($hotels as $hotel) {
            // Randomly select 5-8 questions for this hotel
            $hotelQuestions = Question::all();
            foreach ($hotelQuestions as $index => $question) {
                Faq::create([
                    'hotel_id' => $hotel->id,
                    'question_id' => $question->id,
                    'answer' => $sampleAnswers[$index % count($sampleAnswers)]
                ]);
            }
        }
    }
}
