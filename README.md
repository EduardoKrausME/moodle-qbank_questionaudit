# qbank_questionaudit

`qbank_questionaudit` is a Moodle question bank plugin that audits existing questions. It is intentionally not a
question generator and never changes a question automatically.

The plugin adds three native question-bank entry points:

- an **Audit question** action on each accessible question;
- an **Audit selected questions** bulk action;
- an **Audit this category** control for the current question category.

## Question types analysed

the plugin supports semantic auditing for:

- Multiple choice (`multichoice`)
- True/false (`truefalse`)
- Short answer (`shortanswer`)
- Numerical (`numerical`)
- Essay (`essay`)
- Matching (`match`), when the question data can be represented as complete matching pairs

Unsupported question types are not sent to AI and receive an informational local finding instead.

## Deterministic checks first

Before any AI request, PHP checks the normalized question for issues including:

- empty question statement;
- empty or non-meaningful question name;
- missing correct answer where the qtype requires one;
- suspicious or inconsistent fractions;
- literal duplicate answers;
- empty alternatives or matching pairs;
- missing feedback;
- simple unbalanced HTML structures;
- known minimum structural requirements for supported qtypes.

These checks are intentionally conservative. The HTML check, for example, is a simple balance check and is not intended
to replace a complete HTML validator.

## AI review

After deterministic analysis, supported questions are sent to `local_ai_bridge` for semantic review. The request asks
the model to inspect:

- ambiguous wording;
- more than one defensible answer;
- wording that reveals the correct answer;
- implausible distractors;
- grammatical clues;
- unusually different alternative lengths;
- confusing negation and double negation;
- approximate cognitive level;
- clarity;
- unnecessary bias;
- missing context;
- incompatibility between the statement and the marked correct answer;
- contradictory feedback;
- possible factual obsolescence.

The bridge is required to return strict JSON in this structure:

```json
{
  "summary": "...",
  "findings": [
    {
      "severity": "warning",
      "category": "ambiguity",
      "confidence": "high",
      "evidence": "...",
      "explanation": "...",
      "suggestion": "..."
    }
  ]
}
```

Every response is validated. Severity and confidence values are restricted to known enums, fields must have the expected
types, and every AI finding must contain textual evidence that is actually present in the normalized question content.
Invalid AI output is ignored rather than displayed as a trusted result.

AI findings are review hypotheses, not facts. The report explicitly keeps confidence visible and does not automatically
apply any suggestion.

## Security and privacy

The plugin uses Moodle question capabilities and category contexts. A user cannot audit a question that they cannot
view. Category audits include only the latest question versions that the current user can view.

The plugin does not send question-attempt data or student answers to AI in the plugin.

The plugin does not persist raw prompts, raw AI responses or audit results in its own database tables. It declares a
null privacy provider because it stores no user data itself. `local_ai_bridge` remains responsible for its own routing,
usage accounting and privacy behaviour.

Audit actions require a valid Moodle session key because an AI request may consume tenant credits even though the
question itself is read-only.
