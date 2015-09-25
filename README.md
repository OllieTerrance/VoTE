# VoTE

The Vote-oriented Tally Engine (shortened to 'VoTE') is an interesting new (well, not really) concept.  It will ask you a variety of questions, designed to test what kind of person you are.  There are no right or wrong answers here (although some questions clearly have right and wrong answers) - just vote for what you like.

## Requirements

* [Medoo](http://medoo.in)

## Database schema

```sql
CREATE TABLE "questions" (
  "id" INTEGER NOT NULL ,
  "question" TEXT NOT NULL,
  "answers" TEXT NOT NULL,
  PRIMARY KEY ("id")
);
CREATE TABLE "suggestions" (
  "ip" TEXT NOT NULL,
  "question" TEXT NOT NULL,
  "answers" TEXT NOT NULL
);
CREATE TABLE "votes" (
  "id" INTEGER NOT NULL,
  "ip" TEXT NOT NULL,
  "answer" TEXT NOT NULL,
  PRIMARY KEY (id, ip),
  FOREIGN KEY (id) REFERENCES questions(id)
);
```
