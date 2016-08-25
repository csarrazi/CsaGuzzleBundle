Contributing Guidelines
=======================

Choosing the right branch
-------------------------

Before working on a patch, you must determine on which branch you need to work:

* `1.3`, if you are fixing a bug for an existing feature (you may have to choose a higher branch if the feature you are fixing was introduced in a later version)
* `master`, if you are adding a new and backward incompatible feature.

Working on your code
--------------------

Fork, then clone the repo:

    git clone git@github.com/your-username/CsaGuzzleBundle.git

Create your working branch, based on the correct branch (replace `1.3` with the correct target branch):

    git checkout -b BRANCH_NAME -t origin/1.3
    
`BRANCH_NAME` should be descriptive (`ticket_XXX` where `XXX` is the ticket number is a good convention for bug fixes).

Install dependencies:

    composer update

Make sure the tests pass:

    vendor/bin/phpunit

Push to your fork and [submit a pull request](https://github.com/csarrazi/CsaGuzzleBundle/compare/).

At this point you're waiting on me. I like to at least comment on pull requests within
three business days (and, typically, one business day). I may suggest some changes or
improvements or alternatives.

Some things that will increase the chance that your pull request is accepted:

* Write tests.
* Follow [PSR-1](http://www.php-fig.org/psr/psr-1/) and [PSR-2](http://www.php-fig.org/psr/psr-2/).
* Write a [good commit message](http://tbaggery.com/2008/04/19/a-note-about-git-commit-messages.html).

Pull request template
---------------------

```markdown
| Q             | A
| ------------- | ---
| Bug fix?      | [yes|no]
| New feature?  | [yes|no]
| BC breaks?    | [yes|no]
| Deprecations? | [yes|no]
| Tests pass?   | [yes|no]
| Fixed tickets | [comma separated list of tickets fixed by the PR]
| License       | Apache License 2.0
```

Notes
-----

All bug fixes merged into maintenance branches are also merged into more recent
branches on a regular basis. For instance, if you submit a patch for the 1.3 branch,
the patch will also be applied by the core team on the master branch.
