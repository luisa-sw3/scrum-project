<?php

// tests/BackendBundle/Entity/ItemRepositoryTest.php

namespace Tests\BackendBundle\Entity;

use BackendBundle\Entity\User;
use BackendBundle\Entity\Project;
use BackendBundle\Entity\Sprint;
use BackendBundle\Entity\Item;

class ItemRepositoryTest extends \PHPUnit_Framework_TestCase {

    protected $user;
    protected $user1;
    protected $project;
    protected $sprint;
    protected $items;

    protected function setUp() {
        $user = new User();
        $user->setName('Luisa');
        $user->setLastname('Pereira');
        $user->setEmail('lpereira@kijho.com');
        $user->setPassword('123');
        $this->user = $user;

        $user1 = new User();
        $user1->setName('Andres');
        $user1->setLastname('Ramirez');
        $user1->setEmail('xxx@kijho.com');
        $user1->setPassword('123');
        $this->user1 = $user1;

        $startDate = strtotime("00:00pm February 21 2016");

        $project = new Project();
        $project->setName('p1');
        $project->setStartDate(date("Y-m-d h:i:sa", $startDate));
        $this->project = $project;

        $startSprintDate = strtotime("00:00pm February 25 2016");
        $expectedDate = strtotime("00:00pm March 21 2016");
        $sprint = new Sprint();
        $sprint->setProject($project);
        $sprint->setName('Sprint #1');
        $sprint->setStartDate(date("Y-m-d h:i:sa", $startSprintDate));
        $sprint->setEstimatedDate(date("Y-m-d h:i:sa", $expectedDate));
        $this->sprint = $sprint;

        $item1 = new Item();
        $item1->setSprint($sprint);
        $item1->setTitle('Tarea #1');
        $item1->setType('3');
        $item1->setDesignedUser($user);
        $item1->setEstimatedHours('4');
        $item1->setWorkedHours('6');
        $item1->setStatus('11');

        $item2 = new Item();
        $item2->setSprint($sprint);
        $item2->setTitle('Tarea #2');
        $item2->setType('3');
        $item2->setDesignedUser($user1);
        $item2->setEstimatedHours('4');
        $item2->setWorkedHours('2');
        $item2->setStatus('11');

        $item3 = new Item();
        $item3->setSprint($sprint);
        $item3->setTitle('Solicitud de Cambio #1');
        $item3->setType('6');
        $item3->setDesignedUser($user1);
        $item3->setEstimatedHours('8');
        $item3->setWorkedHours('6');
        $item3->setStatus('5');

        $item4 = new Item();
        $item4->setSprint($sprint);
        $item4->setTitle('Error #1');
        $item4->setType('4');
        $item4->setDesignedUser($user1);
        $item4->setEstimatedHours('10');
        $item4->setWorkedHours('10.5');
        $item4->setStatus('8');

        $item5 = new Item();
        $item5->setSprint($sprint);
        $item5->setTitle('Tarea #3');
        $item5->setType('3');
        $item5->setDesignedUser($user);
        $item5->setEstimatedHours('4');
        $item5->setWorkedHours('3.6');
        $item5->setStatus('5');

        $item6 = new Item();
        $item6->setSprint($sprint);
        $item6->setTitle('Error #2');
        $item6->setType('4');
        $item6->setDesignedUser($user1);
        $item6->setEstimatedHours('5');
        $item6->setWorkedHours('5');
        $item6->setStatus('7');

        $items = array($item1, $item2, $item3, $item4, $item5, $item6);
        $this->items = $items;
    }

    protected function tearDown() {
        $this->user = NULL;
        $this->project = NULL;
        $this->sprint = NULL;
        $this->items = NULL;
    }

    function testTotalDefects() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '4') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals(array($this->items[3], $this->items[5]), $itemType);
    }

    function testTimeOnDefects() {

        $sumSpent = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '4') {
                $sumSpent+=$this->items[$i]->getWorkedHours();
            }
        }

        $this->assertEquals('15.5', $sumSpent);
    }

    function testTotalTasks() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals('3', count($itemType));
    }

    function testTimeOnItems() {

        $sumSpent = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            $sumSpent+=$this->items[$i]->getWorkedHours();
        }

        $this->assertEquals('33.10', $sumSpent);
    }

    function testTimeOnTasks() {

        $sumSpent = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3') {
                $sumSpent+=$this->items[$i]->getWorkedHours();
            }
        }

        $this->assertEquals('11.6', $sumSpent);
    }

    function testTotalDoneTasks() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getStatus() == '11') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals('2', count($itemType));
    }

    function testEstimatedTimeOnTasks() {

        $sumSpent = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3') {
                $sumSpent+=$this->items[$i]->getEstimatedHours();
            }
        }

        $this->assertEquals('12', $sumSpent);
    }

    function testEstimatedTimeOnDefects() {

        $sumSpent = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '4') {
                $sumSpent+=$this->items[$i]->getEstimatedHours();
            }
        }

        $this->assertEquals('15', $sumSpent);
    }

    function testTotalTasksPerUser() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals('1', count($itemType));
    }

    function testTotalDoneTasksByUser() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getStatus() == '11' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals('1', count($itemType));
    }

    function testTotalDefectsPerUser() {

        $itemType = array();

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '4' && $this->items[$i]->getDesignedUser()->getName() == 'Luisa') {
                array_push($itemType, $this->items[$i]);
            }
        }

        $this->assertEquals('0', count($itemType));
    }

    function testTotalNotDoneTasksPerUser() {

        $totalItem = array();
        $doneItem = array();


        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($totalItem, $this->items[$i]);
            }
        }

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getStatus() == '11' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($doneItem, $this->items[$i]);
            }
        }

        $this->assertEquals('0', count($totalItem) - count($doneItem));
    }

    function testProductivityPerUser() {

        $totalItem = array();
        $doneItem = array();


        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($totalItem, $this->items[$i]);
            }
        }

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '3' && $this->items[$i]->getStatus() == '11' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($doneItem, $this->items[$i]);
            }
        }

        $this->assertEquals('100', (count($doneItem) / count($totalItem)) * 100);
    }

    function testTimeDifference() {

        $totalTime = 0;
        $estimatedTime = 0;

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getDesignedUser()->getName() == 'Luisa') {
                $totalTime+=$this->items[$i]->getWorkedHours();
                $estimatedTime+=$this->items[$i]->getEstimatedHours();
            }
        }

        $this->assertEquals('1.6', $totalTime - $estimatedTime);
    }

    function testDefectDensityPerUser() {

        $foundDefects = array();
        $totalItems = array();


        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getDesignedUser()->getName() == 'Andres' && $this->items[$i]->getStatus() == '11') {
                array_push($totalItems, $this->items[$i]);
            }
        }

        for ($i = 0; $i < count($this->items); $i++) {
            if ($this->items[$i]->getType() == '4' && $this->items[$i]->getDesignedUser()->getName() == 'Andres') {
                array_push($foundDefects, $this->items[$i]);
            }
        }

        $this->assertEquals('2', count($foundDefects) / count($totalItems));
    }

}
