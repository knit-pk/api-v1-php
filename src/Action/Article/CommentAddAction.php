<?php
declare(strict_types=1);

namespace App\Action\Article;

use App\Entity\Article;
use App\Entity\Comment;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\HttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;

class CommentAddAction
{

    /**
     * @Route(name="api_articles_comments_post_subresource",
     *      path="/articles/{id}/comments",
     *      defaults={
     *          "_api_resource_class"=Article::class,
     *          "_api_item_operation_name"="add_comment",
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
        if(!$data instanceof Article) {
            throw new \DomainException('Expected instance of Article');
        }

        if('json' !== $request->getContentType()) {
            throw new HttpException(400, sprintf('Content type %s is not supported. Accepted: json', $request->getContentType()));
        }

        $content = json_decode($request->getContent(), true);

        if(empty($content['text'])) {
            throw new HttpException(400, 'Comment cannot be empty.');
        }

        $comment = new Comment();
        $comment->setArticle($data);
        $comment->setAuthor($user);
        $comment->setText($content['text']);

        return $comment;
    }
}