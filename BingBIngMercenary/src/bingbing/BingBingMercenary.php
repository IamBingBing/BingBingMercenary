<?php
namespace bingbing;
use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\Player;
use pocketmine\utils\Config;
use pocketmine\event\server\DataPacketReceiveEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\network\mcpe\protocol\ModalFormResponsePacket;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\command\CommandSender;
use pocketmine\command\Command;
use bingbing\Entity\Mercenaray;
use revivalpmmp\pureentities\PureEntities;
use pocketmine\level\Position;

class BingBingMercenary extends PluginBase implements Listener{
    public $player = [];
    public $isfight= [];
    public function onEnable(){
        $this->getServer()->getPluginManager()->registerEvents($this, $this);
        @mkdir($this->getDataFolder());
        $this->database = new Config($this->getDataFolder()."config.yml", Config::YAML);
        $this->db = $this->database->getAll();
        $this->db = [];
        
        //ntity::registerEntity("Mercenaray");
    }
    public function join (PlayerJoinEvent$event){
        $player = $event->getPlayer();
    }
    public function makeMercenary(Player $player  ) {
       
    }
    public function UI (DataPacketReceiveEvent $event){
        $pk = $event->getPacket();
        $player = $event->getPlayer();
        if ($pk instanceof ModalFormResponsePacket){
            
        }
        
    }
    public function move(PlayerMoveEvent $event) {
        if (!$this->isfight) {
            $this->player[$event->getPlayer()->getName()] = new Mercenaray($event->getPlayer(), $event->getPlayer()->asVector3());
        }
        
       
    }
    public function create(Player $player){
        $mervenary = PureEntities::create(Player::init(), $player->asPosition());
        
    }
    public function damage (EntityDamageByEntityEvent$event){
        if ($event->getDamager() instanceof Player && isset($this->db[$event->getDamager()][0] )){
            $this->isfight[] = true;
        }
    }
    public function onCommand(CommandSender $sender, Command $command, string $label, array $args) :bool{
        return true;
    }
    
}

