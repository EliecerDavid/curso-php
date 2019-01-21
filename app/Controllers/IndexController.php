<?php

namespace App\Controllers;

use App\Models\Job;
use App\Models\Project;

class IndexController extends BaseController
{
    public function indexAction()
    {
        $jobs = Job::all();

        $project1 = new Project('Project 1', 'Description 1');
        $projects = [
            $project1,
        ];

        $name = 'Eliecer David';
        $limitMonths = 2000;

        return $this->renderHTML('index.twig', compact('jobs', 'projects', 'name', 'limitMonths'));
    }
}
