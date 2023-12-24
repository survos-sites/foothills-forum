<?php

namespace App\EventListener;

use App\Entity\Article;
use App\Entity\Author;
use Knp\Menu\ItemInterface;
use Survos\ApiGrid\Service\DatatableService;
use Survos\BootstrapBundle\Event\KnpMenuEvent;
use Survos\BootstrapBundle\Service\ContextService;
use Survos\BootstrapBundle\Traits\KnpMenuHelperTrait;
use Symfony\Bundle\SecurityBundle\Security;
use Symfony\Component\DependencyInjection\Attribute\Autowire;
use Symfony\Component\EventDispatcher\Attribute\AsEventListener;
use Symfony\Component\OptionsResolver\OptionsResolver;
use Symfony\Component\Security\Core\Authorization\AuthorizationCheckerInterface;

//#[AsEventListener(event: KnpMenuEvent::SIDEBAR_MENU, method: 'appSidebarMenu')]
#[AsEventListener(event: KnpMenuEvent::NAVBAR_MENU, method: 'navbarMenu')]
#[AsEventListener(event: KnpMenuEvent::NAVBAR_MENU2, method: 'navbarMenu2')]
#[AsEventListener(event: KnpMenuEvent::FOOTER_MENU, method: 'footerMenu')]
#[AsEventListener(event: KnpMenuEvent::AUTH_MENU, method: 'ourAuthMenu')]
#[AsEventListener(event: KnpMenuEvent::PAGE_MENU, method: 'pageMenu')]
//#[AsEventListener(event: KnpMenuEvent::PAGE_MENU_EVENT, method: 'coreMenu')]
final class AppMenuEventListener
{
    use KnpMenuHelperTrait;

    public function __construct(
        #[Autowire('%kernel.environment%')] private string $env,
        private ContextService $contextService,
        private Security $security,
        private DatatableService $datatableService,
        private ?AuthorizationCheckerInterface $authorizationChecker=null
    )
    {
//        $this->setAuthorizationChecker($this->authorizationChecker);
    }

    public function supports(KnpMenuEvent $event): bool
    {
        return true;
    }

    public function ourAuthMenu(KnpMenuEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $menu = $event->getMenu();
        $this->authMenu($this->authorizationChecker, $this->security, $menu);
    }

    public function pageMenu(KnpMenuEvent $event): void
    {
        $menu = $event->getMenu();
        if ($class = $event->getOption('entityClass')) {
            $this->addFieldMenu($menu, $class);
        }
    }

    public function addFieldMenu(ItemInterface $menu, string $class)
    {
        // hack, we really need an index map.  Also, move this to GridController
        $index = (new \ReflectionClass($class))->getShortName();
        $subMenu = $this->addSubmenu($menu, $index);
        foreach (['survos_index_stats'] as $route) {
            $this->add($subMenu, $route, ['indexName' => $index]);
        }

        $fields = $this->datatableService->getSettingsFromAttributes($class);
        foreach ($fields as $code=>$field) {
            if ($field['browsable']??false) {
                $this->add($menu, 'survos_facet_show',
                    ['indexName' => $index, 'fieldName' => $code],
                    label: $code,
                    translationDomain: null
                );
            }
        }


    }


    public function footerMenu(KnpMenuEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $menu = $event->getMenu();

//        $subMenu = $this->addSubmenu($menu, 'songs');
//

        $theme = $this->contextService->getOption('theme');
        $this->add($menu, 'app_homepage');
        $this->add($menu, 'survos_commands');
        $this->add($menu, uri: '#', label: 'theme: ' . $theme);
        // it should be possible to do this in twig, not here.
        $this->add($menu, id: 'copyright',

            label: 'Data Copyright &copy; <b>Foothills Forum</b> All rights reserved.');
    }

    public function navbarMenu2(KnpMenuEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $menu = $event->getMenu();
        $this->add($menu, 'survos_commands');
//        $this->add($menu, 'app_articles_with_doctrine')
        $submenu = $this->addSubmenu($menu, 'Articles');
//        $this->add($submenu, 'article_browse');
//        $this->add($submenu, 'article_index');
        // add github
    }


    public function navbarMenu(KnpMenuEvent $event): void
    {
        if (!$this->supports($event)) {
            return;
        }
        $menu = $event->getMenu();
        $this->add($menu, 'author_index');
//        $this->addMenuItem($menu, ['route' => 'song_index', 'label' => "Songs", 'icon' => 'fas fa-home']);
//        $this->addMenuItem($menu, ['route' => 'song_browse', 'label' => "Song Search", 'icon' => 'fas fa-search']);
//        $subMenu = $this->addSubmenu($menu, 'songs');
//        $subMenu->setExtra('btn', 'btn btn-danger');
//        // either a button on a navlink
//        $subMenu->setLinkAttribute('class', 'nav-link');

//        $this->add($menu, 'song_index', label: 'Songs');
//        $this->add($menu, 'video_browse', label: 'Youtube Videos');
//        $this->add($menu, 'video_index'); // in-memory
//        $this->add($subMenu, 'song_browse');

//        $this->addMenuItem($menu, ['route' => 'video_index', 'label' => "Videos", 'icon' => 'fas fa-home']);
//        $this->addMenuItem($menu, ['route' => 'video_index', 'label' => "Videos (API)", 'icon' => 'fas fa-sync']);

        foreach ([Author::class/*, Article::class*/] as $class) {
            $name = (new \ReflectionClass($class))->getShortName();
            $subMenu = $this->addSubmenu($menu, $name);
            // @todo: get Crud attributes or controller methods as routes
            foreach (['_browse','_index','_symfony_crud_index', '_new'] as $suffix) {
                $route = strtolower($name) . $suffix;
                $this->add($subMenu, $route);
            }
        }

        if ($this->env === 'dev' && $this->security->isGranted('ROLE_ADMIN')) {
            $subMenu = $this->addSubmenu($menu, 'admin');
            $this->add($subMenu, 'survos_commands', label: "Commands");
        }

//        $nestedMenu = $this->addMenuItem($menu, ['label' => 'Credits']);
//        foreach (['bundles', 'javascript'] as $type) {
//            // $this->addMenuItem($nestedMenu, ['route' => 'survos_base_credits', 'rp' => ['type' => $type], 'label' => ucfirst($type)]);
//            $this->addMenuItem($nestedMenu, ['uri' => "#type" , 'label' => ucfirst($type)]);
//        }
    }

}
