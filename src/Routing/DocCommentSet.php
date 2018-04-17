<?php declare(strict_types=1);

namespace Shitwork\Routing;

use Shitwork\DocComment;

final class DocCommentSet
{
    private $classComment;
    private $methodComment;

    public function __construct(?DocComment $classComment, ?DocComment $methodComment)
    {
        $this->classComment = $classComment;
        $this->methodComment = $methodComment;
    }

    public function hasClassComment(): bool
    {
        return $this->classComment !== null;
    }

    public function getClassComment(): ?DocComment
    {
        return $this->classComment;
    }

    public function hasMethodComment(): bool
    {
        return $this->methodComment !== null;
    }

    public function getMethodComment(): ?DocComment
    {
        return $this->methodComment;
    }
}
