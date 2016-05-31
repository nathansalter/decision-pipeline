<?php
namespace DecisionPipeline;

class DecisionPipeline implements Pipeline
{
    private $pipeline;
    
    private $defaultDecision;

    /**
     * DecisionPipeline constructor. Adds the deciders in a consistent queue to properly define decisions
     * 
     * @param array[] $deciders
     * @param Decision|null $defaultDecision
     */
    public function __construct(array $deciders, Decision $defaultDecision = null)
    {
        if(null === $defaultDecision) {
            $defaultDecision = new NoDecision();
        }
        $this->defaultDecision = $defaultDecision;
        $this->pipeline = [];
        foreach($deciders as $decider) {
            if(! $decider instanceof PipelineDecider && ! is_callable($decider)) {
                throw new \InvalidArgumentException(sprintf('%s is unable to make decisions for pipeline', gettype($decider)));
            }
            $this->pipeline[] = $decider;
        }
    }

    /**
     * Make a decision about the question, and respond with what the deciders return
     * 
     * @param Question $question
     * @return Decision
     */
    public function decide(Question $question) : Decision
    {
        $pipeline = $this->pipeline;
        $defaultDecider = function(Question $question, Decision $decision) {
            return $decision;
        };
        $nextDecision = null;
        $nextDecision = function(Question $question, Decision $decision) use (&$nextDecision, &$pipeline, $defaultDecider) {
            $nextDecider = array_shift($pipeline);
            if(null === $nextDecider) {
                $nextDecider = $defaultDecider;
            }
            if($nextDecider instanceof PipelineDecider) {
                return $nextDecider->decide($question, $decision, $nextDecision);
            } elseif(is_callable($nextDecider)) {
                return $nextDecider($question, $decision, $nextDecision);
            } else {
                // This should never happen because of the validation in the constructor
                throw new \RuntimeException(sprintf('Invalid decider (%s) attempted to be handled by Decision Pipeline', gettype($nextDecider)));
            }
        };
        return $nextDecision($question, $this->defaultDecision, $nextDecision);
    }
}
