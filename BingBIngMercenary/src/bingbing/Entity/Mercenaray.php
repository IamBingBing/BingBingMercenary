<?php
namespace bingbing\Entity;

use pocketmine\math\Vector3;
use pocketmine\math\AxisAlignedBB;
use pocketmine\plugin\Plugin;
use pocketmine\Player;
use pocketmine\event\entity\EntityDamageByEntityEvent;
use pocketmine\network\mcpe\protocol\AddEntityPacket;
class Mercenaray extends Player {
    public $ex;
    public $ey;
    public $ez;
    public $width = 0.6;
    public $length = 0.98;
    public $height = 1.8;
    public $stepHeight = 0.5;
    protected $gravity = 0.25;
    protected $speed = 0.125;
    public $damage;
    public $eyeHeight = 1.62;
    public $canCollide = false;
    public $target;
    public $player;
    public $isFallow = true;
    public $jumpTick = 0;
    public $in = 0;
    public $isHead = false;
    public $targetVec;
    public $owner;
    public function __construct(Player $owner , Vector3 $target ){
        $this->target = $target;
        $this->setSkin($owner->getSkin());
    }
    public function onUpdate(int $currentTick) :bool{
        if($this->closed == true || $this->dead == true || ($target = $this->target) == null || $target->dead || $this->player instanceof Player && !$this->player->spawned){
            if(!$this->closed) parent::close();
            $this->dead = true;
            return false;
        }else{
            $dis = sqrt(pow($dZ = $this->target->z - $this->z, 2) + pow($dX = $this->target->x - $this->x, 2));
            if($this->isHead){
                $bb = clone $this->BoundingBox();
                $this->timings->startTiming();
                $this->lastUpdate = $currentTick;
                if($this->getLevel()->getFolderName() !== $target->getLevel()->getFolderName()) $this->teleport($target);
                $onGround = count($this->level->getCollisionBlocks($bb->offset(0, -$this->gravity, 0))) > 0;
                $x = cos($at2 = atan2($dZ, $dX)) * $this->speed;
                $z = sin($at2) * $this->speed;
                $y = 0;
                $bb->offset(0, 0.25, 0);
                $isJump = count($this->level->getCollisionBlocks($bb->grow(0, 0, 0)->offset($x, 1, $z))) <= 0;
                if(count($this->level->getCollisionBlocks($bb->grow(0, 0, 0)->offset(0, 0, $z))) > 0){
                    $z = 0;
                    if($isJump) $y = $this->gravity;
                }
                if(count($this->level->getCollisionBlocks($bb->grow(0, 0, 0)->offset($x, 0, 0))) > 0){
                    $x = 0;
                    if($isJump) $y = $this->gravity;
                }
                if(!$this->isFallow || $dis < $this->distance){
                    $x = 0;
                    $z = 0;
                }
                if($this->isFallow && $dis > 20){
                    $this->updateMove($target);
                    if($target instanceof Player) $target->sendMessage("[BlockPet] 같이가요 ");
                    return true;
                }elseif(!$isJump && $target->y > $this->y - ($target instanceof Player ? 0.5 : 0)){
                    if($this->jumpTick <= 0) $this->jumpTick = 40;
                    elseif($this->jumpTick > 36) $y = $this->gravity;
                }
                if($this->jumpTick > 0) $this->jumpTick--;
                if(($n = floor($this->y) - $this->y) < $this->gravity && $n > 0) $y = -$n;
                if($y == 0 && !$onGround) $y = -$this->gravity;
                $block = $this->level->getBlock($this->add($vec = new Vector3($x, $y, $z)));
                if($block->hasEntityCollision()){
                    $block->addVelocityToEntity($this, $vec2 = $vec->add(0, $this->gravity, 0));
                    $vec->x = ($vec->x + $vec2->x / 2) / 5;
                    $vec->y = ($vec->y + $vec2->y / 2);
                    $vec->z = ($vec->z + $vec2->z / 2) / 5;
                }
                if(count($this->level->getCollisionBlocks($bb->offset(0, -0.01, 0))) <= 0) $y -= 0.01;
                $this->updateMove($vec->add(new Vector3(($this->boundingBox->minX + $this->boundingBox->maxX - $this->drag) / 2, ($this->boundingBox->minY + $this->boundingBox->maxY) / 2, ($this->boundingBox->minZ + $this->boundingBox->maxZ - $this->drag) / 2)));
                $this->onGround = $onGround;
            }else{
                $x = cos($at2 = atan2($dZ, $dX)) * $this->distance;
                $z = sin($at2) * $this->distance;
                $this->updateMove($target->add(-$x, 0, -$z));
            }
        }
        return true;
    }
    public function updateMove(Vector3 $vec){
        $this->x = $vec->x;
        $this->y = $vec->y;
        $this->z = $vec->z;
        foreach($this->hasSpawned as $player){
            $player->addEntityMovement($this->getId(), $this->x, $this->y, $this->z, 0, 0);
        }
    }
    public function BoundingBox(){
        $this->boundingBox = new AxisAlignedBB($x = $this->x - $this->width / 2, $y = $this->y - $this->stepHeight, $z = $this->z - $this->length / 2, $x + $this->width, $y + $this->height, $z + $this->length);
        return $this->boundingBox;
    }
    public function attacking( $damage , Player $player):void{
        $event = new EntityDamageByEntityEvent($this->owner, $player, EntityDamageByEntityEvent::CAUSE_ENTITY_ATTACK, $damage);
        $player->attack($event);
    }
    public function getX(){
        parent::getX();
        return $this->ex;
    }
    public function getY(){
        parent::getX();
        return $this->ex;
    }
    public function getZ(){
        parent::getX();
        return $this->ex;
    }
    public function levelup():void{
        $this->setMaxHealth($this->getMaxHealth() + 1);
        $this->setHealth($this->getMaxHealth());
        $this->damage++;
    }
    public function holdItem($item){
        $this->getInventory()->setItemInHand($item);
    }
    public function spawnTo(Player $player){
        $pk = new AddEntityPacket();
        $pk->type = Player::NETWORK_ID;
        $pk->putEntityUniqueId($this->id) ;
        $pk->x = $this->x;
        $pk->y = $this->y + $this->stepHeight;
        $pk->z = $this->z;
        $pk->speedX = 0;
        $pk->speedY = 0;
        $pk->speedZ = 0;
        $pk->yaw = 0;
        $pk->pitch = 0;
        $player->dataPacket($pk);
        parent::spawnTo($player);
    }
}