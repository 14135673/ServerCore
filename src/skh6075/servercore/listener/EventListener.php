<?php

namespace skh6075\servercore\listener;

use pocketmine\block\BlockIds;
use pocketmine\block\Flowable;
use pocketmine\event\block\BlockSpreadEvent;
use pocketmine\event\block\LeavesDecayEvent;
use pocketmine\event\entity\EntityDamageEvent;
use pocketmine\event\entity\EntityTeleportEvent;
use pocketmine\event\entity\ItemSpawnEvent;
use pocketmine\event\inventory\InventoryTransactionEvent;
use pocketmine\event\Listener;
use pocketmine\event\player\PlayerChatEvent;
use pocketmine\event\player\PlayerDropItemEvent;
use pocketmine\event\player\PlayerJoinEvent;
use pocketmine\event\player\PlayerMoveEvent;
use pocketmine\event\player\PlayerQuitEvent;
use pocketmine\event\server\DataPacketSendEvent;
use pocketmine\network\mcpe\protocol\DisconnectPacket;
use pocketmine\Player;
use pocketmine\scheduler\ClosureTask;
use pocketmine\Server;
use skh6075\servercore\ServerCore;

final class EventListener implements Listener{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        $this->plugin = $plugin;
    }

    /** @priority HIGHEST */
    public function onBlockSpread(BlockSpreadEvent $event): void{
        $block = $event->getBlock();
        $waters = [BlockIds::WATER, BlockIds::WATER_LILY, BlockIds::STILL_WATER, BlockIds::FLOWING_WATER];
        if (in_array($event->getSource()->getId(), $waters) and $block instanceof Flowable) {
            $event->setCancelled(true);
        }
    }

    /** @priority HIGHEST */
    public function onPlayerDropItem(PlayerDropItemEvent $event): void{
        $player = $event->getPlayer();
        if (!$event->isCancelled()) {
            $event->setCancelled(!$player->isOp() and !$this->plugin->isDropItemWorld(strtolower($player->getLevel()->getFolderName())));
        }
    }

    /** @priority HIGHEST */
    public function onEntityDamage(EntityDamageEvent $event): void{
        if ($event->getCause() === EntityDamageEvent::CAUSE_FALL)
            return;

        /** @var Player $player */
        if (!($player = $event->getEntity()) instanceof Player)
            return;

        if (!$event->isCancelled()) {
            $event->setCancelled(!$this->plugin->isPvpWorld(strtolower($player->getLevel()->getFolderName())));
        }
    }

    /** @priority HIGHEST */
    public function onPlayerJoin(PlayerJoinEvent $event): void{
        $event->setJoinMessage(null);

        $player = $event->getPlayer();
        if (!$player->hasPlayedBefore()) {
            Server::getInstance()->broadcastMessage("§l§f[§b첫접속§f]§r§b " . $player->getName() . "§f님§7 께서 서버에 처음으로 접속하였습니다!");
        } else {
            Server::getInstance()->broadcastPopup("§l§f[§b접속§f]§r§f " . $player->getName());
        }

        ServerCore::getInstance()->getScheduler()->scheduleDelayedTask(new ClosureTask(function () use ($player): void{
            $this->plugin->sendUpdatedGamerule($player, ServerCore::GAMERULES);
        }), 10);
    }

    /** @priority HIGHEST */
    public function onPlayerQuit(PlayerQuitEvent $event): void{
        $event->setQuitMessage(null);

        $player = $event->getPlayer();
        Server::getInstance()->broadcastPopup("§l§f[§b퇴장§f]§r§f " . $player->getName());
    }

    /** @priority HIGHEST */
    public function onPlayerChat(PlayerChatEvent $event): void{
        $player = $event->getPlayer();

        if (!$event->isCancelled()) {
            $event->setCancelled(!$player->isOp() and !$this->plugin->canChatting());
        }
    }

    /** @priority HIGHEST */
    public function onPlayerMove(PlayerMoveEvent $event): void{
        $player = $event->getPlayer();

        if (!$event->isCancelled()) {
            $event->setCancelled(!$player->isOp() and !$this->plugin->canMovement());
        }
    }

    public function onItemSpawn(ItemSpawnEvent $event): void{
        $item = $event->getEntity();

        $ref = new \ReflectionClass($item);
        $property = $ref->getProperty("age");
        $property->setAccessible(true);
        $property->setValue($item, 5100);
    }

    public function onLeaveBreak(LeavesDecayEvent $event): void{
        $event->setCancelled(true);
    }

    public function onDataPacketSend(DataPacketSendEvent $ev){
        $pk = $ev->getPacket();
        if($pk instanceof DisconnectPacket){
            if($pk->message === "Internal server error"){
                $pk->message = "§l§c오류가 발생했습니다.§r\n§r§f아테나서버 밴드로 문의해주세요!";
            }
        }
    }

    public function onInventoryTransaction(InventoryTransactionEvent $event) : void{
        $player = $event->getTransaction()->getSource();
        if($event->isCancelled()){
            $player->getCursorInventory()->sendSlot(0, $player);
        }
    }

    public function onEntityTeleport(EntityTeleportEvent $event): void{
        if (($player = $event->getEntity()) instanceof Player) {
            $player->getLevel()->loadChunk($player->x << 4, $player->y << 4, true);
        }
    }
}