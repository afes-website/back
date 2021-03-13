<?php
namespace Tests;

use App\Models\Reservation;
use App\Models\Term;
use App\Models\User;
use Faker\Provider\DateTime;
use Illuminate\Support\Str;

class EntranceTest extends TestCase {
    public function testEnter() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);
        $guest_id = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => $reservation->id]
        );
        $this->assertResponseOk();
    }

    public function testInvalidGuestCode() {
        $invalid_codes = [];
        $count = 5;

        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);

        for ($i = 0; $i < $count; ++$i) {
            do {
                $prefix = rand(1, 10);
                $id = rand(1, 10);
            } while ($prefix == 2 && $id == 5);
            $invalid_codes[] = Str::random($prefix).'-'.Str::random($id);
        }

        foreach ($invalid_codes as $invalid_code) {
            $this->actingAs($user)->post(
                '/onsite/general/enter',
                ['guest_id' => $invalid_code, 'reservation_id' => $reservation->id]
            );
            $this->assertResponseStatus(400);
            $this->receiveJson();
            $code = json_decode($this->response->getContent())->error_code;
            $this->assertEquals('INVALID_WRISTBAND_CODE', $code);
        }
    }

    public function testAlreadyUsedGuestCode() {
        $count = 5;

        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $used_id = [];
        for ($i = 0; $i < $count; ++$i) {
            $reservation_1 = factory(Reservation::class)->create([
                'term_id' => $term->id
            ]);
            $reservation_2 = factory(Reservation::class)->create([
                'term_id' => $term->id
            ]);
            do {
                $guest_id = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
            } while (in_array($guest_id, $used_id));
            $used_id[] = $guest_id;

            $this->actingAs($user)->post(
                '/onsite/general/enter',
                ['guest_id' => $guest_id, 'reservation_id' => $reservation_1->id]
            );

            $this->assertResponseOk();

            $this->actingAs($user)->post(
                '/onsite/general/enter',
                ['guest_id' => $guest_id, 'reservation_id' => $reservation_2->id]
            );

            $this->assertResponseStatus(400);
            $this->receiveJson();
            $code = json_decode($this->response->getContent())->error_code;
            $this->assertEquals('ALREADY_USED_WRISTBAND', $code);
        }
    }

    public function testReservationNotFound() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $guest_id = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => 'R-'.Str::random(7)]
        );

        $this->assertResponseStatus(400);
        $this->receiveJson();
        $code = json_decode($this->response->getContent())->error_code;
        $this->assertEquals('RESERVATION_NOT_FOUND', $code);
    }

    //   INVALID_RESERVATION_INFO: NO TEST

    public function testAlreadyEnteredReservation() {
        $count = 5;

        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $used_id = [];
        for ($i = 0; $i < $count; ++$i) {
            $reservation = factory(Reservation::class)->create([
                'term_id' => $term->id
            ]);

            do {
                $guest_id_1 = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
            } while (in_array($guest_id_1, $used_id));
            $used_id[] = $guest_id_1;

            do {
                $guest_id_2 = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
            } while (in_array($guest_id_2, $used_id));
            $used_id[] = $guest_id_2;

            $this->actingAs($user)->post(
                '/onsite/general/enter',
                ['guest_id' => $guest_id_1, 'reservation_id' => $reservation->id]
            );

            $this->assertResponseOk();

            $this->actingAs($user)->post(
                '/onsite/general/enter',
                ['guest_id' => $guest_id_1, 'reservation_id' => $reservation->id]
            );

            $this->assertResponseStatus(400);
            $this->receiveJson();
            $code = json_decode($this->response->getContent())->error_code;
            $this->assertEquals('ALREADY_ENTERED_RESERVATION', $code);
        }
    }

    public function testOutOfReservationTime() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create([
            'enter_scheduled_time'=>DateTime::dateTimeBetween('-1 year', '-1 day'),
            'exit_scheduled_time'=>DateTime::dateTimeBetween('-1 year', '-1 day')
        ]);
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);
        $guest_id = config('onsite.guest_types')[$term->guest_type]['prefix']."-".Str::random(5);
        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => $reservation->id]
        );

        $this->assertResponseStatus(400);
        $this->receiveJson();
        $code = json_decode($this->response->getContent())->error_code;
        $this->assertEquals('OUT_OF_RESERVATION_TIME', $code);

        $term = factory(Term::class)->create([
            'enter_scheduled_time'=>DateTime::dateTimeBetween('+1 day', '+1 year'),
            'exit_scheduled_time'=>DateTime::dateTimeBetween('+1 day', '+1 year')
        ]);

        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => $reservation->id]
        );

        $this->assertResponseStatus(400);
        $this->receiveJson();
        $code = json_decode($this->response->getContent())->error_code;
        $this->assertEquals('OUT_OF_RESERVATION_TIME', $code);
    }

    public function testWrongWristbandColor() {
        $user = factory(User::class, 'general')->create();
        $term = factory(Term::class)->create();
        $reservation = factory(Reservation::class)->create([
            'term_id' => $term->id
        ]);
        $guest_id = "XX" . "-" . Str::random(5); // 存在しないリストバンド prefix
        $this->actingAs($user)->post(
            '/onsite/general/enter',
            ['guest_id' => $guest_id, 'reservation_id' => $reservation->id]
        );

        $this->assertResponseStatus(400);
        $this->receiveJson();
        $code = json_decode($this->response->getContent())->error_code;
        $this->assertEquals('WRONG_WRISTBAND_COLOR', $code);
    }
}
