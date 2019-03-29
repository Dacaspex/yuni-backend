<?php

namespace App\Command;

use App\Models\Category;
use App\Models\OperatingTimes;
use App\Models\Schedule;
use App\Storage\Factory;
use App\Storage\Storage;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;

class SeedCommand extends Command
{
    /**
     * @var Storage
     */
    private $storage;
    /**
     * @var array
     */
    private $canteens;
    /**
     * @var array
     */
    private $items;
    /**
     * @var array
     */
    private $canteenItemMap;
    /**
     * @var Schedule
     */
    private $defaultSchedule;
    /**
     * @var array
     */
    private $canteenReviews;
    /**
     * @var array
     */
    private $menuItemReviews;

    public function __construct()
    {
        parent::__construct();

        $this->storage = Factory::getStorage();
    }

    /**
     * @inheritdoc
     */
    protected function configure()
    {
        $this
            ->setName('db:seed')
            ->setDescription('Clears the database and seeds it with default data');
    }

    /**
     * @inheritdoc
     */
    protected function initialize(InputInterface $input, OutputInterface $output)
    {
        // Build canteens
        $operatingTimes = new OperatingTimes(
            [
                'MONDAY'    => 900,
                'WEDNESDAY' => 900,
                'FRIDAY'    => 830
            ],
            [
                'MONDAY'    => 1700,
                'WEDNESDAY' => 1700,
                'FRIDAY'    => 1300
            ]
        );

        $this->canteens = [
            [
                'name'            => 'Auditorium Canteen',
                'description'     => 'Description for Auditorium canteen',
                'building'        => 'auditorium',
                'longitude'       => 0.0,
                'latitude'        => 0.0,
                'operating_times' => $operatingTimes
            ],
            [
                'name'            => 'Metaforum Canteen',
                'description'     => 'Description for Metaforum canteen',
                'building'        => 'metaforum',
                'longitude'       => 0.0,
                'latitude'        => 0.0,
                'operating_times' => $operatingTimes
            ],
            [
                'name'            => 'Test Canteen',
                'description'     => 'Description for Test canteen',
                'building'        => 'test',
                'longitude'       => 0.0,
                'latitude'        => 0.0,
                'operating_times' => $operatingTimes
            ],
        ];

        $this->items = [
            [
                'name'        => 'Apple',
                'description' => 'Not a pineapple',
                'category'    => Category::OTHER(),
            ],
            [
                'name'        => 'Peer',
                'description' => 'Delicious peer',
                'category'    => Category::OTHER(),
            ],
            [
                'name'        => 'Coffee',
                'description' => 'Fuel for the day',
                'category'    => Category::DRINKS(),
            ],
            [
                'name'        => 'Tea',
                'description' => 'Better than coffee',
                'category'    => Category::DRINKS(),
            ],
            [
                'name'        => 'Cheese sandwich',
                'description' => 'Typically Dutch!',
                'category'    => Category::SANDWICH(),
            ],
            [
                'name'        => 'Meat sandwich',
                'description' => 'Delicious meal that can pull you through the day',
                'category'    => Category::SANDWICH(),
            ],
        ];

        $this->canteenItemMap = [
            1 => [1, 2, 3, 4],
            2 => [2, 3],
            3 => [1, 4, 6]
        ];

        $this->defaultSchedule = Schedule::fromBitMask('1111111');

        $this->canteenReviews = [
            [
                'rating'      => 2.5,
                'description' => 'Average atmosphere, wouldn\'t eat here again.',
            ],
            [
                'rating'      => 1.0,
                'description' => 'Absolutely the worst canteen I have ever eaten at.',
            ],
            [
                'rating'      => 4.5,
                'description' => 'Perfect customer satisfaction :) Great canteen!.',
            ],
            [
                'rating'      => 4.0,
                'description' => 'The food was good',
            ],
            [
                'rating'      => 4.5,
                'description' => 'Decent canteen, can recommend',
            ],
            [
                'rating'      => 2.0,
                'description' => 'I expected more to be honest',
            ],
            [
                'rating'      => 2.5,
                'description' => 'I am confusion',
            ],
        ];

        $this->menuItemReviews = [
            [
                'rating'      => 1.0,
                'description' => 'Simply gross',
            ],
            [
                'rating'      => 2.5,
                'description' => 'Meh, it is ok I guess',
            ],
            [
                'rating'      => 3.0,
                'description' => 'Did not taste as good as I hoped',
            ],
            [
                'rating'      => 3.0,
                'description' => 'Kinda ok',
            ],
            [
                'rating'      => 3.5,
                'description' => 'It was ok :)',
            ],
            [
                'rating'      => 4.0,
                'description' => 'I definitely can recommend this item to anyone who orders it!',
            ],
            [
                'rating'      => 4.0,
                'description' => 'Ok',
            ],
            [
                'rating'      => 4.0,
                'description' => 'Good',
            ],
            [
                'rating'      => 5.0,
                'description' => 'Godlike :O',
            ]
        ];
    }

    /**
     * @inheritdoc
     */
    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $io = new SymfonyStyle($input, $output);

        $io->section('Cleanup');
        $io->write('Clearing database ');
        $this->storage->truncateAll();
        $io->writeln('<info>Done</info>');

        $io->section('Seeding');

        // Canteens
        $io->write('Canteens ');
        foreach ($this->canteens as $canteen) {
            $this->storage->createCanteen(
                $canteen['name'],
                $canteen['description'],
                $canteen['building'],
                $canteen['longitude'],
                $canteen['latitude'],
                $canteen['operating_times']
            );
        }
        $io->writeln('<info>Done</info>');

        // Menu items
        $io->write('Menu items ');
        foreach ($this->items as $item) {
            $this->storage->createMenuItem(
                $item['name'],
                $item['description'],
                $item['category']
            );
        }
        $io->writeln('<info>Done</info>');
        $io->write('Linking canteens and menu items ');
        foreach ($this->canteenItemMap as $canteenId => $menuItemIds) {
            foreach ($menuItemIds as $menuItemId) {
                $this->storage->addMenuItemToMenu($canteenId, $menuItemId, $this->defaultSchedule);
            }
        }
        $io->writeln('<info>Done</info>');

        // Canteen reviews
        $io->write('Canteen reviews ');
        for ($i = 0; $i < count($this->canteens); $i++) {
            $n = rand(0, 3);
            for ($j = 0; $j <= $n; $j++) {
                $review = $this->canteenReviews[rand(0, count($this->canteenReviews) - 1)];
                $this->storage->createCanteenReview(
                    $i,
                    $review['rating'],
                    $review['description']
                );
            }
        }
        $io->writeln('<info>Done</info>');

        // Menu item reviews
        $io->write('Menu item reviews ');
        for ($i = 0; $i < count($this->items); $i++) {
            $n = rand(0, 3);
            for ($j = 0; $j <= $n; $j++) {
                $review = $this->menuItemReviews[rand(0, count($this->menuItemReviews) - 1)];
                $this->storage->createMenuItemReview(
                    $i,
                    $review['rating'],
                    $review['description']
                );
            }
        }
        $io->writeln('<info>Done</info>');

        $io->write('Busyness ');
        for ($i = 0; $i < count($this->canteens); $i++) {
            $n = rand(0, 4);
            for ($j = 0; $j < $n; $j++) {
                $this->storage->createBusynessEntry($i);
            }
        }
        $io->writeln('<info>Done</info>');

        $io->success('Seeding successfully completed');
    }
}