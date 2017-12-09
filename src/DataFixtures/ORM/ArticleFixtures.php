<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentReply;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;

class ArticleFixtures extends Fixture
{
    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     */
    public function load(ObjectManager $manager): void
    {
        $categoryCollection = CategoryFixtures::PUBLIC_CATEGORY_CODES;
        $usersCollection = UserFixtures::PUBLIC_USERNAMES;
        $tagCollection = TagFixtures::PUBLIC_TAG_CODES;
        $tagCollectionTotalItems = \count($tagCollection);

        /** @var \App\Entity\Image $image */
        $image = $this->getReference('image-card-photo-4.jpg');

        for ($i = 1; $i <= 10; ++$i) {
            shuffle($usersCollection);
            /** @var \App\Entity\User $author */
            $author = $this->getReference(sprintf('user-%s', $usersCollection[0]));

            shuffle($categoryCollection);
            /** @var \App\Entity\Category $category */
            $category = $this->getReference(sprintf('category-%s', $categoryCollection[0]));

            $articleTitle = sprintf('Article %d', $i);

            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setContent(sprintf('Awesome short %s about.', $articleTitle));
            $article->setDescription(sprintf('Awesome %s content.', $articleTitle));
            $article->setAuthor($author);
            $article->setCategory($category);
            $article->setImage($image);

            // Add random number of random tags
            shuffle($tagCollection);
            $tagsPerArticle = random_int(1, $tagCollectionTotalItems);
            foreach ($tagCollection as $randomTag) {
                /** @var \App\Entity\Tag $tag */
                $tag = $this->getReference(sprintf('tag-%s', $randomTag));
                $article->addTag($tag);
                if (0 === --$tagsPerArticle) {
                    break;
                }
            }

            $manager->persist($article);
        }

        // Predefined articles
        foreach ($this->getArticlesData() as $data) {
            $article = new Article();
            if (isset($data['image'])) {
                $article->setImage($this->getReference($data['image']));
            }
            $article->setTitle($data['title']);
            $article->setAuthor($this->getReference($data['author']));
            $article->setCategory($this->getReference($data['category']));
            $article->setContent($data['content']);
            $article->setDescription($data['description']);

            foreach ($data['tags'] as $tag) {
                $article->addTag($this->getReference($tag));
            }

            if (isset($data['comments'])) {
                foreach ($data['comments'] as $commentData) {
                    $comment = new Comment();
                    $comment->setAuthor($this->getReference($commentData['author']));
                    $comment->setText($commentData['text']);
                    $comment->setArticle($article);

                    if (isset($commentData['replies'])) {
                        foreach ($commentData['replies'] as $replyData) {
                            $reply = new CommentReply();
                            $reply->setText($replyData['text']);
                            $reply->setAuthor($this->getReference($replyData['author']));
                            $reply->setComment($comment);

                            $manager->persist($reply);
                        }
                    }

                    $manager->persist($comment);
                }
            }

            $manager->persist($article);
        }

        $manager->flush();
    }

    public function getArticlesData(): array
    {
        return [
            [
                'title'       => 'Jak zacząć naukę języka Kotlin',
                'content'     => 'Kotlin is a language I thought about using for some time, yet it wasn\'t until recently that I decided to give it a try. And I\'m so excited that I have!.

## This is a random subheading 
If you\'ve not heard of Kotlin, it\'s a language that, while newer than most, has gained significant amounts of traction in recent years. And why not? It has a series of advantages, especially over Java, that makes it quite compeling.


Here\'s short selection:
* It\'s developed by and experienced software company, JetBrains
* It\'s a first-class langouage for Android
* It\'s influenced by Java, Scala, Groovy, C#, Gosu, JavaScript, and Swift.
* It\'s interchangeable with Java.
* Data classes: These automatically have `equals()`, `hashCode()`, `toString()`, and `copy()` functions.


And These are just a **small selection** If you want more, definetely check out this list from authority:


```
(defn- -init
([info message]
[[message] (atom (into {} info))])
([info message ex]
[[message ex] (atom (into {} info))]))

(defn- -deref
[^CustomException this]
@(.info this))

(defn- -getInfo
[this]
@this)

(defn- -addInfo
[^CustomException this key value]
(swap! (.info this) assoc key value))
```

> You are probably aware that this will not trigger an update. In fact, the holy guide of Vue explicitly mentions caveats to arrays. Why is this so? Becaouse setters on arrays have no means of detecting asignments by index.

One option to handle this is to use Vue.set

```
Vue.set(this.names, 0, \'John Elway\');
```

Hovewer, Vue is kind enough to wrap a __few array methods__ for us, so we can update our arrays using those array methods.

```
this.names.push(\'John Elway\');
```

## Another subheading

Not the best example right?, I probably should have already knew that this property would exist, but there are cases that we might not knot the property name. JavaScript loosed typedness allows us to add properties with ease. VUe reactivity, however, has no cloure that we added this property.
![Image of Yaktocat](https://octodex.github.com/images/yaktocat.png) 
*Description to an image*


## Conclusion

I\'m typing this shortly before the release of 2.5 Vue 3 hasn\'t been talked about a whole lot, but I am really looking forward to it due to the changes mentioned above. That being said, I won\'t be able to use it on work projects for the immediate future. Why? Vue 3 cannot be used with Internet Explorer and Babel\'s not going to be able to fix that.

Lorem Ipsum jest tekstem stosowanym jako przykładowy wypełniacz w przemyśle poligraficznym. Został po raz pierwszy użyty w XV w. przez nieznanego drukarza do wypełnienia tekstem próbnej książki. Pięć wieków później zaczął być używany przemyśle elektronicznym, pozostając praktycznie niezmienionym. Spopularyzował się w latach 60. XX w. wraz z publikacją arkuszy Letrasetu, zawierających fragmenty Lorem Ipsum, a ostatnio z zawierającym różne wersje Lorem Ipsum oprogramowaniem przeznaczonym do realizacji druków na komputerach osobistych, jak Aldus PageMaker.',
                'description' => 'Naucz się języka już dziś! Pokażemy Ci jak osiągnąć sukces z językiem Kotlin.',
                'category'    => 'category-article',
                'tags'        => [
                    'tag-university',
                    'tag-programming',
                    'tag-poland',
                ],
                'image'       => 'image-card-photo-4.jpg',
                'comments'    => [
                    [
                        'author'  => 'user-reader',
                        'text'    => 'Super artykuł!',
                        'replies' => [
                            [
                                'author' => 'user-user_writer',
                                'text'   => 'Dzięki',
                            ],
                            [
                                'author' => 'user-reader',
                                'text'   => '@user-user_writer Nie ma za co!',
                            ],
                        ],
                    ],
                    [
                        'author' => 'user-user',
                        'text'   => 'O kurde, nigdy tego nie ogarnę..',
                    ],
                ],
                'author'      => 'user-user_writer',
            ],
        ];
    }

    public function getDependencies(): array
    {
        return [
            TagFixtures::class,
            CategoryFixtures::class,
            UserFixtures::class,
            ImageFixtures::class,
        ];
    }
}
