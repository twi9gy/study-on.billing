<?php

namespace App\DataFixtures;

use App\Entity\Course;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;

class CourseFixtures extends Fixture
{
    public function load(ObjectManager $manager): void
    {
        $courses = [
            1 => [
                'code' => 'Introduction-to-Data-Analysis-and-Machine-Learning',
                'type' => 1,
                'cost' => 100,
            ],
            2 =>[
                'code' => 'Web-Designer',
                'type' => 2,
            ],
            3 =>[
                'code' => 'Internet-Marketer',
                'type' => 3,
                'cost' => 500,
            ],
            4 =>[
                'code' => 'Business-Analyst',
                'type' => 1,
                'cost' => 50,
            ],
        ];

        // Запись объектов
        foreach ($courses as $course) {
            // Создание курса
            $newCourse = new Course();
            $newCourse->setCode($course['code']);
            $newCourse->setType($course['type']);
            if (isset($course['cost'])) {
                $newCourse->setCost($course['cost']);
            }
            $manager->persist($newCourse);
        }
        $manager->flush();
    }
}
