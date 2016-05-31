<?php

use DecisionPipeline\Decision;
use DecisionPipeline\DecisionPipeline;
use DecisionPipeline\ExampleDecider;
use DecisionPipeline\NoDecision;
use DecisionPipeline\PipelineDecider;
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
                return $next($expectedQuestion, $decision);
            },
            function(Question $question, Decision $decision, callable $next = null) use ($expectedQuestion) {
                $this->assertSame($expectedQuestion, $question);
                return $next($question, $decision);
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
        $expectedError = 'This is not a deciding class';
        $decider = new class($expectedError) implements PipelineDecider
        {
            private $expectedError;
            public function __construct($expectedError)
            {
                $this->expectedError = $expectedError;
            }

            public function decide(Question $question, Decision $decision, callable $next = null) : Decision
            {
                throw new \RuntimeException($this->expectedError);
            }
        };
        $this->setExpectedException(\RuntimeException::class, $expectedError);
        $errorPipeline = new DecisionPipeline([$decider]);
        $errorPipeline->decide($this->getMock(Question::class));
    }
}
