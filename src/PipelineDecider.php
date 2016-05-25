<?php
namespace DecisionPipeline;

interface PipelineDecider
{
    /**
     * @param Question $question
     * @param Decision $decision
     * @param callable|null $next
     * @return Decision
     */
    public function decide(Question $question, Decision $decision, callable $next = null);
}
