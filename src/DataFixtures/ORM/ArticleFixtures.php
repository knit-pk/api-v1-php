<?php

declare(strict_types=1);

namespace App\DataFixtures\ORM;

use App\Entity\Article;
use App\Entity\Comment;
use App\Entity\CommentReply;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\DataFixtures\DependentFixtureInterface;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\ORM\EntityManagerInterface;

class ArticleFixtures extends Fixture implements DependentFixtureInterface
{
    private $connection;

    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->connection = $entityManager->getConnection();
    }

    /**
     * Load data fixtures with the passed EntityManager.
     *
     * @param ObjectManager $manager
     *
     * @throws \RuntimeException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\DBAL\DBALException
     */
    public function load(ObjectManager $manager): void
    {
        $this->createTriggers();

        $categoryCollection = CategoryFixtures::PUBLIC_CATEGORY_CODES;
        $usersCollection = UserFixtures::PUBLIC_USERNAMES;
        $tagCollection = TagFixtures::PUBLIC_TAG_CODES;
        $tagCollectionTotalItems = \count($tagCollection);

        /** @var \App\Entity\Image $image */
        $image = $this->getReference('image-card-photo-4.jpg');

        for ($i = 1; $i <= 10; ++$i) {
            \shuffle($usersCollection);
            /** @var \App\Entity\User $commentAuthor */
            $commentAuthor = $this->getReference(\sprintf('user-%s', $usersCollection[0]));

            \shuffle($categoryCollection);
            /** @var \App\Entity\Category $category */
            $category = $this->getReference(\sprintf('category-%s', $categoryCollection[0]));

            $articleTitle = \sprintf('Article %d', $i);

            $article = new Article();
            $article->setTitle($articleTitle);
            $article->setContent(\sprintf('Awesome short %s about.', $articleTitle));
            $article->setDescription(\sprintf('Awesome %s content.', $articleTitle));
            $article->setAuthor($commentAuthor);
            $article->setCategory($category);
            $article->setImage($image);

            // Add random number of random tags
            \shuffle($tagCollection);
            $tagsPerArticle = \random_int(1, $tagCollectionTotalItems);
            foreach ($tagCollection as $randomTag) {
                /** @var \App\Entity\Tag $tagReference */
                $tagReference = $this->getReference(\sprintf('tag-%s', $randomTag));
                $article->addTag($tagReference);
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
                /** @var \App\Entity\Image $image */
                $image = $this->getReference($data['image']);
                $article->setImage($image);
            }
            $article->setTitle($data['title']);

            /** @var \App\Entity\User $author */
            $author = $this->getReference($data['author']);
            $article->setAuthor($author);

            /** @var \App\Entity\Category $category */
            $category = $this->getReference($data['category']);
            $article->setCategory($category);
            $article->setContent($data['content']);
            $article->setDescription($data['description']);

            foreach ($data['tags'] as $tagReference) {
                /** @var \App\Entity\Tag $tag */
                $tag = $this->getReference($tagReference);
                $article->addTag($tag);
            }

            if (isset($data['comments'])) {
                foreach ($data['comments'] as $commentData) {
                    $comment = new Comment();
                    /** @var \App\Entity\User $commentAuthor */
                    $commentAuthor = $this->getReference($commentData['author']);
                    $comment->setAuthor($commentAuthor);
                    $comment->setText($commentData['text']);
                    $comment->setArticle($article);

                    if (isset($commentData['replies'])) {
                        foreach ($commentData['replies'] as $replyData) {
                            $reply = new CommentReply();
                            /** @var \App\Entity\User $replyAuthor */
                            $replyAuthor = $this->getReference($replyData['author']);
                            $reply->setText($replyData['text']);
                            $reply->setAuthor($replyAuthor);
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
                'title' => 'Jak zacząć naukę języka Kotlin',
                'content' => 'Kotlin is a language I thought about using for some time, yet it wasn\'t until recently that I decided to give it a try. And I\'m so excited that I have!.

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
                'category' => 'category-article',
                'tags' => [
                    'tag-university',
                    'tag-programming',
                    'tag-poland',
                ],
                'image' => 'image-card-photo-4.jpg',
                'comments' => [
                    [
                        'author' => 'user-reader',
                        'text' => 'Super artykuł!',
                        'replies' => [
                            [
                                'author' => 'user-user_writer',
                                'text' => 'Dzięki',
                            ],
                            [
                                'author' => 'user-reader',
                                'text' => '@user-user_writer Nie ma za co!',
                            ],
                        ],
                    ],
                    [
                        'author' => 'user-user',
                        'text' => 'O kurde, nigdy tego nie ogarnę..',
                    ],
                ],
                'author' => 'user-user_writer',
            ],
            [
                'title' => 'Wykład o systemie kontroli wersji Git',
                'content' => 'Wykład obejmował:
- instalacja i konfiguracja
- commit history
- podstawy rozgałęziania i scalania
- zmiana bazy
- techniki zaawansowane: fitrowanie gałęzi, cherry pick, refs',
                'description' => 'Wczoraj odbył się wykład o Git poprowadzony przez zarząd KNIT',
                'category' => 'category-news',
                'tags' => [
                    'tag-university',
                    'tag-programming',
                    'tag-poland',
                ],
                'image' => 'image-card-photo-4.jpg',
                'comments' => [
                    [
                        'author' => 'user-reader',
                        'text' => 'A może zrobicie wykład o sposobie pracy z Git?',
                    ],
                    [
                        'author' => 'user-user',
                        'text' => 'A ja mało zrozumiałem :< Macie jakieś materiały do samodzielnej nauki?',
                    ],
                    [
                        'author' => 'user-writer',
                        'text' => 'Dobra robota chłopaki!',
                    ],
                    [
                        'author' => 'user-writer',
                        'text' => 'Super wykład! Chciałbym spytać czy seria wykładów o Git będzie kontynuowana?',
                        'replies' => [
                            [
                                'author' => 'user-articles_author',
                                'text' => 'Prawdopodobnie zrealizujemy jeszcze dwa wykłady. Jeden o `git flow` czyli o sposobie pracy ze zdalnym repozytorium oraz o technikach zaawansowanych :)',
                            ],
                            [
                                'author' => 'user-writer',
                                'text' => 'Dokładnie o to mi chodziło. Nie mogę się doczekać! :)',
                            ],
                        ],
                    ],
                    [
                        'author' => 'user-reader',
                        'text' => 'Super! Świetny wykład, dzięki niemu zrozumiałem różnicę pomiedzy `merge` a `rebase`!',
                    ],
                    [
                        'author' => 'user-user_writer',
                        'text' => 'No, i teraz wszystko jasne! Dzięki!',
                    ],
                    [
                        'author' => 'user-reader',
                        'text' => 'Mam mały problem. Zrobiłem commit zmian które nie są jeszcze w pełni ukończone. Czy mogę jakoś cofnąć ten commit i zrobić go ponownie jak skończę pracę?',
                        'replies' => [
                            [
                                'author' => 'user-articles_author',
                                'text' => 'Jasne, jest kilka rozwiązań tego problemu:
1. Możesz kontynuować pracę, a gdy już skończyć zrobić `git commit --amend`. To polecenie nadpisze ostatni commit zmianami które właśnie zatwierdzasz. *Uwaga!* `git commit --amend` tworzy zupełnie nowy commit, dlatego trzeba uważać z tym poleceniem jeśli wypchałeś już zmiany do zdalnego repozytorium.
2. Jak skończysz pracę możesz zrobić drugi commit, a później scalić te dwa commity w jeden używając interaktywnego rebase `git rebase -i HEAD~2`, co weźmie dwa ostatnie commity i otworzy edytor z listą tych dwóch commitów. Aby zrobić sqash commita do poprzedniego będziesz musiał zmienić `pick` na `s` lub `squash` przy danym commicie. 
Jest jeszcze kilka innych, ale bardziej skomplikowanych sposobów, powodzenia! :)',
                            ],
                            [
                                'author' => 'user-reader',
                                'text' => 'A co jeśli chcę całkowicie usunąć ostatni commit? Przez przypadek wypchałem zmiany testowe. Da się tak zrobic? Zrobiłem tak w pracy i jak tego nie usunę to szef mnie zabije! :c',
                            ],
                            [
                                'author' => 'user-articles_author',
                                'text' => 'Spokojnie! W Gitcie i na to jest sposób :) Wystarczy, że zrobisz `git rest --hard HEAD^`, co oznacza cofnięcie historii do przedostatniego commita. Jeżeli zmiany są już na zdalnym repozytorium, będziesz musiał zrobić force push `git push -f`. *Uwaga! Force push może być bardzo niebezpieczny jeśli zmiany są publiczne. Może doprowadzić do zaburzenia całej historii commitów. Dlatego upewnij się, że nikt nie rozpoczął pracy wychodząc od twojego brancha.*',
                            ],
                            [
                                'author' => 'user-reader',
                                'text' => '@articles_author zrobiłem tym pierszym sposobem. Super sprawa, dzięki!',
                            ],
                            [
                                'author' => 'user-reader',
                                'text' => '@articles_author uff! Udało się, cofnąłem ten commit. Dzięk! Btw super strona. Bardzo szybko działa. Jakich technologii używacie?',
                            ],
                            [
                                'author' => 'user-articles_author',
                                'text' => '@reader cześć front-endowa zrobiona jest w Nuxt.js - framework bazujący na Vue.js do tworzenia aplikacji z wykorzystaniem SSR (Server Side Rendering). To właśnie niemu oraz API napisanym w PHP na frameworku API Platform, z odpowiednią wartwą cache (Varnish), zawdzięczamy szybkość działanai naszej platformy.',
                            ],
                            [
                                'author' => 'user-reader',
                                'text' => 'Wow! Czy kod waszej strony jest open source?',
                            ],
                            [
                                'author' => 'user-articles_author',
                                'text' => 'Tak, repozytorium dostępne jest [tutaj](https://github.com/knit-pk/homepage-nuxtjs/). Zapraszamy wszystkich zainteresowanych do współpracy. Jest jeszcze wiele do zrobienia i można się na prawdę dużo nauczyć :)',
                            ],
                            [
                                'author' => 'user-articles_author',
                                'text' => '@articles_author mam doświadczenie w pracy z Reactem. Jest możliwość dołączenia do zespołu?',
                            ],
                            [
                                'author' => 'user-articles_author',
                                'text' => '@reader pewnie! Napisz do @admin, on ci wszystko wytłumaczy :)',
                            ],
                        ],
                    ],
                ],
                'author' => 'user-user_writer',
            ],
        ];
    }

    /**
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceNotFoundException
     * @throws \Symfony\Component\DependencyInjection\Exception\ServiceCircularReferenceException
     * @throws \Doctrine\DBAL\DBALException
     */
    private function createTriggers()
    {
        $driverName = $this->connection->getDriver()->getName();
        $sqlName = 'create_comment_triggers';
        $sqlFile = \sprintf('%s/../Resources/sql/%s/%s.sql', __DIR__, $driverName, $sqlName);

        if (!\is_file($sqlFile)) {
            dump(\sprintf('Notice: SQL File %s could not be found for driver %s', $sqlName, $driverName));

            return;
        }

        $this->connection->exec(\file_get_contents($sqlFile));
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
