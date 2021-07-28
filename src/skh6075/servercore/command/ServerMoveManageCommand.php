<?php

namespace skh6075\servercore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use skh6075\servercore\ServerCore;

class ServerMoveManageCommand extends Command{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        parent::__construct("움직임관리", "움직임관리 명령어 입니다.");
        $this->setPermission("server.move.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $label, array $args): bool{
        if (!$this->testPermission($player)) {
            $player->sendMessage(ServerCore::$prefix . "당신은 이 명령어를 사용할 권한이 없습니다.");
            return false;
        }
        $value = array_shift($args) ?? "";
        $types = ["켜기" => true, "끄기" => false];
        if (trim($value) === "" and !in_array($value, array_keys($types))) {
            $player->sendMessage(ServerCore::$prefix . "/움직임관리 [켜기/끄기]");
            return false;
        }
        $this->plugin->setMovement($types[$value]);
        $result = $this->plugin->canMovement() ? "불가능" : "가능";
        Server::getInstance()->broadcastMessage(ServerCore::$prefix . "관리자에 의해 §f움직임f이 §f" . $result . "§7 으(로) 변경되었습니다.");
        return true;
    }
}