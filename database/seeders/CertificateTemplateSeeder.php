<?php

namespace Database\Seeders;

use App\Models\CertificateTemplate;
use Illuminate\Database\Seeder;

class CertificateTemplateSeeder extends Seeder
{
    public function run()
    {
        CertificateTemplate::create([
            'slug'      => 'course-default',
            'name'      => 'Course Completion Certificate',
            'view_name' => 'admin.certificates.templates.course_completion'
        ]);

        CertificateTemplate::create([
            'slug'      => 'training-default',
            'name'      => 'Training Completion Certificate',
            'view_name' => 'admin.certificates.templates.training_completion'
        ]);

        CertificateTemplate::create([
            'slug'      => 'quiz-default',
            'name'      => 'Quiz Certificate',
            'view_name' => 'admin.certificates.templates.quiz_completion'
        ]);
    }
}
