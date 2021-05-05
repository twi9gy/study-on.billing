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
            // Арендные курсы
            [
                'title' => 'Введение в анализ данных и машинное обучение.',
                'code' => 'Introduction-to-Data-Analysis-and-Machine-Learning',
                'type' => 1,
                'cost' => 100,
            ],
            [
                'title' => 'Профессия Спортивный менеджер.',
                'code' => 'Sport-Manager',
                'type' => 1,
                'cost' => 300,
            ],
            [
                'title' => 'Бизнес-аналитик.',
                'code' => 'Business-Analyst',
                'type' => 1,
                'cost' => 50,
            ],
            [
                'title' => 'Основы скетчинга.',
                'code' => 'Sketching-Basics',
                'type' => 1,
                'cost' => 275,
            ],
            // Бесплатные курсы
            [
                'title' => 'Веб-дизайнер.',
                'code' => 'Web-Designer',
                'type' => 2,
            ],
            [
                'title' => 'Оформление и защита прав на интеллектуальную собственность.',
                'code' => 'Protection-of-rights',
                'type' => 2,
            ],
            // Покупные курсы
            [
                'title' => 'Интернет-маркетолог с нуля.',
                'code' => 'Internet-Marketer',
                'type' => 3,
                'cost' => 500,
            ],
            [
                'title' => 'Adobe Photoshop. Основы.',
                'code' => 'Adobe-Photoshop',
                'type' => 3,
                'cost' => 350,
            ],
            [
                'title' => 'Эффективное управление личными финансами',
                'code' => 'Financial-management',
                'type' => 3,
                'cost' => 50,
            ],
        ];

        // Запись объектов
        foreach ($courses as $course) {
            // Создание курса
            $newCourse = new Course();
            $newCourse->setTitle($course['title']);
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
