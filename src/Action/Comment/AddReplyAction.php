<?php
declare(strict_types=1);

namespace App\Action\Comment;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentReply;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class AddReplyAction
{

    /**
     * @Route(name="api_comments_comment_replies_post_subresource",
     *      path="/comments/{id}/comment_replies",
     *      defaults={
     *          "_api_resource_class"=Comment::class,
     *          "_api_item_operation_name"="add_reply",
     *      },
     * )
     * @Method("POST")
     *
     * @param \Symfony\Component\HttpFoundation\Request           $request
     * @param \Symfony\Component\Security\Core\User\UserInterface $user
     * @param                                                     $data
     *
     * @return mixed
     *
     * @throws \Symfony\Component\HttpKernel\Exception\HttpException
     * @throws \LogicException
     * @throws \DomainException
     */
    public function __invoke(Request $request, UserInterface $user, $data)
    {
        if (!$data instanceof Comment) {
            throw new \DomainException('Expected instance of Comment');
        }

        if ('json' !== $request->getContentType()) {
            throw new HttpException(400, sprintf('Content type %s is not supported. Accepted: json', $request->getContentType()));
        }

        $content = json_decode($request->getContent(), true);

        if (empty($content['text'])) {
            throw new HttpException(400, 'Reply cannot be empty.');
        }

        $reply = new CommentReply();
        $reply->setComment($data);
        $reply->setAuthor($user);
        $reply->setText($content['text']);

        return $reply;
    }
}