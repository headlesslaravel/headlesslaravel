<?php

namespace HeadlessLaravel\Formations\Tests\FilterTests;

use HeadlessLaravel\Formations\Tests\Fixtures\Models\Post;
use HeadlessLaravel\Formations\Tests\Fixtures\PostFormation;
use HeadlessLaravel\Formations\Tests\TestCase;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Route;

class BoundsTest extends TestCase
{
    use RefreshDatabase;

    public function setUp(): void
    {
        parent::setUp();

        Route::get('/posts', function (PostFormation $request) {
            return $request->results();
        });
    }

    public function test_filtering_bounds_north_west()
    {
        $building = Post::create([
            'title' => 'Chrysler Building',
            'latitude' => '40.75178128662803',
            'longitude' => '-73.97552835820437',
        ]);

        $zoo = Post::create([
            'title' => 'Central Park Zoo',
            'latitude' => '40.76850772506696',
            'longitude' => '-73.97186950177363',
        ]);

        $this->get('posts?ne_lat=40.75895998394&ne_lng=-73.96345894132793&sw_lat=40.75330670079011&sw_lng=-73.97384512739167')
            ->assertJsonCount(0, 'data');

        $this->get('posts?ne_lat=40.77684517172769&ne_lng=-73.94029698966872&sw_lat=40.740142194040715&sw_lng=-73.99667825933423')
            ->assertJsonCount(2, 'data');

        $this->get('posts?ne_lat=40.77201243589006&ne_lng=-73.95794432009787&sw_lat=40.76391535908882&sw_lng=-73.97979922108213')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $zoo->id);

        $this->get('posts?ne_lat=40.75555971122113&ne_lng=-73.96922446090224&sw_lat=40.74683062112093&sw_lng=-73.98124075728896')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $building->id);
    }

    public function test_filtering_bounds_south_west()
    {
        $building = Post::create([
            'title' => 'Juan B. Castagnino Fine Arts Museum',
            'latitude' => '-32.953003735023934',
            'longitude' => '-60.65654316932009',
        ]);

        $park = Post::create([
            'title' => 'Parque Nacional Los Glaciares',
            'latitude' => '-50.33040490491672',
            'longitude' => '-73.2336946329897',
        ]);

        $this->get('posts?ne_lat=-50.35774174526334&ne_lng=-55.67011426840107&sw_lat=-52.73464153556662&sw_lng=-62.04218394534642')
            ->assertJsonCount(0, 'data');

        $this->get('posts?ne_lat=-29.437224547271814&ne_lng=-50.792185597788524&sw_lat=-53.47334791958085&sw_lng=-84.93769031141166')
            ->assertJsonCount(2, 'data');

        $this->get('posts?ne_lat=-50.328377601011745&ne_lng=-73.22955330268377&sw_lat=-50.33228145374079&sw_lng=-73.24315746545041')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $park->id);

        $this->get('posts?ne_lat=-32.942884066185&ne_lng=-60.629978573886156&sw_lat=-32.96369835344826&sw_lng=-60.67370930589393')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $building->id);
    }

    public function test_filtering_bounds_north_east()
    {
        $building = Post::create([
            'title' => 'The Great Pyramid of Giza',
            'latitude' => '29.9795194199079',
            'longitude' => '31.134130933576174',
        ]);

        $monument = Post::create([
            'title' => 'Great Sphinx of Giza',
            'latitude' => '29.975470588406807',
            'longitude' => '31.1376029386518',
        ]);

        $this->get('posts?ne_lat=29.97489437478326&ne_lng=31.140328062702743&sw_lat=29.973806994926363&sw_lng=31.137377633080636')
            ->assertJsonCount(0, 'data');

        $this->get('posts?ne_lat=29.98260648132918&ne_lng=31.1465339206095&sw_lat=29.97110099123436&sw_lng=31.126578287165277')
            ->assertJsonCount(2, 'data');

        $this->get('posts?ne_lat=29.976152166838588&ne_lng=31.13929396844806&sw_lat=29.974854486223276&sw_lng=31.136297842982245')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $monument->id);

        $this->get('posts?ne_lat=29.98017868554015&ne_lng=31.134865871252263&sw_lat=29.97863297965456&sw_lng=31.132949232167512')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $building->id);
    }

    public function test_filtering_bounds_south_east()
    {
        $building = Post::create([
            'title' => 'Sydney Opera House',
            'latitude' => '-33.856242636716274',
            'longitude' => '151.21514577056155',
        ]);

        $monument = Post::create([
            'title' => 'Mrs Macquarie\'s Chair',
            'latitude' => '-33.85947869493282',
            'longitude' => '151.22258714189954',
        ]);

        $this->get('posts?ne_lat=-33.854042507972395&ne_lng=151.22703982425048&sw_lat=-33.85585394386985&sw_lng=151.2236467398207')
            ->assertJsonCount(0, 'data');

        $this->get('posts?ne_lat=-33.855623922393335&ne_lng=151.22731681073452&sw_lat=-33.862093040108284&sw_lng=151.2095896757545')
            ->assertJsonCount(2, 'data');

        $this->get('posts?ne_lat=-33.85821162828574&ne_lng=151.22350824657866&sw_lat=-33.86022423421637&sw_lng=151.2214654712587')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $monument->id);

        $this->get('posts?ne_lat=-33.85568142782055&ne_lng=151.21682594765068&sw_lat=-33.857722845401305&sw_lng=151.21336361659985')
            ->assertJsonCount(1, 'data')
            ->assertJsonPath('data.0.id', $building->id);
    }
}
