### Moje uwagi do zadania

Ogólnie bardzo przyjemnie, że gotowe repozytorium  z README.md. Mega plus. Zazwyczaj w takich zadaniach spotykałem się z podejściem: "napisz nam Twittera od zera".

**NOTE:** w tej notatce będę podawał SHA commitów, postaram się je zaktualizować na koniec, gdybym robił jakieś rebase'y czy squashe, ale jakbym coś pominął to proszę o wyrozumiałość.

Jako, że wolę konteneryzację i chciałem uniknąć instalowania rzeczy na moim hoście, pomyślałem o opcji dwa: docker compose z README. 

Natomiast nie wszystko działało od ręki, jest kilka rzeczy, które warto byłoby poprawić:

1. Przy uruchomieniu, może to kwestia MacOS, może mojej złej konfiguracji (ale PHP i powiązane instalowałem na świeżo):  


        Warning: require(/var/www/html/vendor/autoload.php): failed to open stream: No such file or directory in /var/www/html/config/bootstrap.php on line 5
        
        Fatal error: require(): Failed opening required '/var/www/html/vendor/autoload.php' (include_path='.:/usr/local/lib/php') in /var/www/html/config/bootstrap.php on line 5  
  
    Bierze się to z faktu, że jeżeli nie mamy na hoście composer i nie zrobimy `composer install` w zamontowanym katalogu z repozytorium nie będzie katalogu `vendor` z potrzebnymi skryptami. 

    Jako że jest zaznaczone w treści zadania, że mamy nie dodawać nowych paczek, śmiało można zablokować katalog `vendors` tak jak w propozycji rozwiązania.

    Fix w commicie (b6a31a1)

1. W `docker-compose.yml` jest już zbędny props `version`  
       
         WARN[0000] /Users/lgroszkowski/Devel/tests/telemedi/recruitment_task_fullstack/docker-compose.yml: the attribute `version` is obsolete, it will be ignored, please remove it to avoid potential confusion

      Fix w commicie 05234ba.

1. Po rozwiązaniu powyższych pojawia się problem:
    Twig\Error\RuntimeError:
        
        An exception has been thrown during the rendering of a template ("Could not find the entrypoints file from Webpack: the file "/var/www/html/public/build/entrypoints.json" does not exist.").

        at templates/base.html.twig:14
        at Twig\Template->displayBlock('stylesheets', array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'stylesheets' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_stylesheets'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body'), 'javascripts' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_javascripts')))
            (var/cache/dev/twig/e0/e0d303b95f6f55954f91c89126ecbff958337c795fd7f43c21dfc4e548390912.php:60)
        at __TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c->doDisplay(array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'stylesheets' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_stylesheets'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body'), 'javascripts' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_javascripts')))
            (vendor/twig/twig/src/Template.php:394)
        at Twig\Template->displayWithErrorHandling(array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'stylesheets' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_stylesheets'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body'), 'javascripts' => array(object(__TwigTemplate_2e72fb4e18e56ff12453d5f2ec28c1792b4a23177482a01b2e1602e54242424c), 'block_javascripts')))
            (vendor/twig/twig/src/Template.php:367)
        at Twig\Template->display(array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body')))
            (var/cache/dev/twig/07/07f953addfd2d1a46a7caa9388f987a4585f0c19b51d03de9f844b214b37324f.php:46)
        at __TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503->doDisplay(array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body')))
            (vendor/twig/twig/src/Template.php:394)
        at Twig\Template->displayWithErrorHandling(array('app' => object(AppVariable)), array('title' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_title'), 'body' => array(object(__TwigTemplate_5bc42c273f9cc0540703f2ae209c74238c392c5f112ca501e1419ab6dfe28503), 'block_body')))
            (vendor/twig/twig/src/Template.php:367)
        at Twig\Template->display(array())
            (vendor/twig/twig/src/Template.php:379)
        at Twig\Template->render(array(), array())
            (vendor/twig/twig/src/TemplateWrapper.php:40)
        at Twig\TemplateWrapper->render(array())
            (vendor/twig/twig/src/Environment.php:280)
        at Twig\Environment->render('exchange_rates/app-root.html.twig', array())
            (vendor/symfony/framework-bundle/Controller/ControllerTrait.php:235)
        at Symfony\Bundle\FrameworkBundle\Controller\AbstractController->render('exchange_rates/app-root.html.twig')
            (src/App/Controller/DefaultController.php:18)
        at App\Controller\DefaultController->index()
            (vendor/symfony/http-kernel/HttpKernel.php:169)
        at Symfony\Component\HttpKernel\HttpKernel->handleRaw(object(Request), 1)
            (vendor/symfony/http-kernel/HttpKernel.php:81)
        at Symfony\Component\HttpKernel\HttpKernel->handle(object(Request), 1, true)
            (vendor/symfony/http-kernel/Kernel.php:201)
        at Symfony\Component\HttpKernel\Kernel->handle(object(Request))
            (public/index.php:27)

    Sytuacja nieco podobna do `vendors` - w dockerze jest budowana zawartość katalogu `public/build` ale jest ona przesonięta zamontowanym katalogiem. Można to zrobić poprzez serwis który skopiuje pliki po starcie kontenera, ale lepiej jest przygotować drugi kontener, który bęzie budował i aktualizował pliki w katalogu `public/build` - dzięki temu pozbędziemy się konieczności posiadania na hoście nodejs w konkretnej wersji i innych zależności.

    Pierwsze rozwiązanie w commitcie (26eb7a4).

    Drugie (nadpisujące pierwsze) w commicie (3bd053f).

1. Brakowało jakiegoś "ogarniacza" do code-style. Minimalistycznie `.editorconfig` dodany w commicie (967bc0c)

1. Idąc od TDD, brakowało mi jakiegoś prostego sposobu uruchomienia testów automatycznych. Podobnie jak wcześniej - musiałbym instalować na hoście, więc dodałem profil `test` w docker-compose.yml, choć jest to dyskusyjne - może chcielibyśmy, żeby się to odpalało przy każdym `docker compose up -d`? Ogólnie zabieg ten ma też spory sens w perspektywie późniejszego montowania tego w CI/CD - taki zabieg dość konkretnie ułatwi integrację np. z GitLab CI/CD, a jednocześnie utrzyma izolację skryptu CI/CD, a faktycznej implementacji testów automatycznych (warstwa abstrakcji, tj. będzie można wymienić zawartość docker-compose.yml).

    Dodane w commicie 8b965aa.


### Po zrobieniu zadania

Ogólnie można by jeszcze popracować nad kodem, pewnie znajdą się gdzieś miejsca do optymalizacji i zrobienia czystszego kodu. Zajęło mi to takie dobre 2d+ i trochę mi już starczy. Fajnie byłoby dodać np. lokalizację i wszystkie human-readable stringi przerzucić do jakiejś libki typu `i18n`, dodać Jest po stronie UI do testów. Ogólnie jeszcze można by ten kod męczyć i rozbudowywać. Widzę, że pomyliłem się z pkt. 5 - nie wyświetlam na jednej stronie kursów wszystkich walut oraz kursów aktualnych, natomiast jest to zrealizowane w podstronach danej waluty. Gdyby to było zadanie biznesowe to bym poprawiał, ale skoro to zadanie rekrutacyjne, to może jest już ok?

Aha i commitów też nie podpisywałem z lenistwa.
