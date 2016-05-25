# decision-pipeline
Middleware-esque way of making decisions instead of using Event Managers

## Usage ##
Usage of this is very simple. Providing the pipeline a question object, to decide on will pass this question through
to all of the decision actors (deciders). Each decider in turn chooses to either make an ultimate decision or make a
decision which may be overridden at a later stage.

## Example ##

Preferable usage is to create your own Question and Decision classes, but if using PHP7 then you MAY use anonymous
classes. Deciders passed into the Pipeline MUST either implement PipelineDecider or be a closure.

    $pipeline = new DecisionPipeline([
        function (Question $question, Decision $decision, callable $next = null) {
            if($question->cannot()) {
                throw new \RuntimeException('I cannot!');
            }
            return $next($question, $decision);
        },
        new SpecialDecider()
    ]);
    $decision = $pipeline->decide(new SpecialQuestion());
    

If no decision is made, the NoDecision class will be returned. You MAY return a custom default decision by simply 
passing it in as the second parameter to the constructor.

    $pipeline = new DecisionPipeline([], new SpecialDefaultDecision());
    

Each decider is ALWAYS run in order that it is passed into the constructor, so any priority MUST be set in the 
constructor.