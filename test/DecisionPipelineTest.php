<?php

use DecisionPipeline\Decision;
use DecisionPipeline\DecisionPipeline;
use DecisionPipeline\ExampleDecider;
use DecisionPipeline\NoDecision;
use DecisionPipeline\Question;

class DecisionPipelineTest extends PHPUnit_Framework_TestCase
{


    public function testEmptyPipelineDecidesNoDecision()
    {
        $emptyPipeline = new DecisionPipeline([]);
        $decision = $emptyPipeline->decide($this->getMock(Question::class));
        $this->assertInstanceOf(NoDecision::class, $decision);
    }

    public function testDefaultDecisionCanBeOverridden()
    {
        $expectedDecision = $this->getMock(Decision::class);
        $emptyPipeline = new DecisionPipeline([], $expectedDecision);
        $actualDecision = $emptyPipeline->decide($this->getMock(Question::class));
        $this->assertSame($expectedDecision, $actualDecision);
    }

    public function testDecisionMade()
    {
        $expectedDecision = $this->getMock(Decision::class);
        $decidingPipeline = new DecisionPipeline([
            function(Question $question, Decision $decision, callable $next = null) use ($expectedDecision) {
                return $expectedDecision;
            }
        ]);
        $actualDecision = $decidingPipeline->decide($this->getMock(Question::class));
        $this->assertSame($expectedDecision, $actualDecision);
    }
    
    public function testDecisionOverridden()
    {
        $unexpectedDecision = $this->getMock(Decision::class);
        $expectedDecision = $this->getMock(Decision::class);
        $decidingPipeline = new DecisionPipeline([
            function(Question $question, Decision $decision, callable $next = null) use ($unexpectedDecision) {
                return $next($question, $unexpectedDecision);
            },
            function(Question $question, Decision $decision, callable $next = null) use ($expectedDecision) {
                return $next($question, $expectedDecision);
            }
        ]);
        $actualDecision = $decidingPipeline->decide($this->getMock(Question::class));
        $this->assertNotSame($unexpectedDecision, $actualDecision);
        $this->assertSame($expectedDecision, $actualDecision);
    }

    public function testQuestionOverridden()
    {
        $expectedQuestion = $this->getMock(Question::class);
        $unexpectedQuestion = $this->getMock(Question::class);
        $decidingPipeline = new DecisionPipeline([
            function(Question $question, Decision $decision, callable $next = null) use ($unexpectedQuestion, $expectedQuestion) {
                $this->assertSame($unexpectedQuestion, $question);
                $next($expectedQuestion, $decision);
            },
            function(Question $question, Decision $decision, callable $next = null) use ($expectedQuestion) {
                $this->assertSame($expectedQuestion, $question);
                $next($question, $decision);
            }
        ]);
        $decidingPipeline->decide($unexpectedQuestion);
    }

    /**
     * @param $invalidDecider
     * @dataProvider provideInvalidDeciders
     */
    public function testRejectsInvalidDeciders($invalidDecider)
    {
        $this->setExpectedException(\InvalidArgumentException::class);
        new DecisionPipeline([$invalidDecider]);
    }

    public function provideInvalidDeciders()
    {
        return [
            ['ksjd'],
            [new stdClass],
            [[]],
            [null]
        ];
    }

    public function testPipelineCanDecideWithClasses()
    {
        $decider = new ExampleDecider();
        $this->setExpectedException(\RuntimeException::class, ExampleDecider::EXAMPLE_ERROR);
        $errorPipeline = new DecisionPipeline([$decider]);
        $errorPipeline->decide($this->getMock(Question::class));
    }
}
