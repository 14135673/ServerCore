<?php

namespace skh6075\servercore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use pocketmine\Server;
use skh6075\servercore\ServerCore;

class ServerChatManageCommand extends Command{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        parent::__construct("채팅관리", "채팅관리 명령어 입니다.");
        $this->setPermission("server.chat.permission");
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
            $player->sendMessage(ServerCore::$prefix . "/채팅관리 [켜기/끄기]");
            return false;
        }
        $this->plugin->setChatting($types[$value]);
        $result = $this->plugin->canChatting() ? "불가능" : "가능";
        Server::getInstance()->broadcastMessage(ServerCore::$prefix . "관리자에 의해 §f채팅 사용§f이 §f" . $result . "§7 으(로) 변경되었습니다.");
        return true;
    }
}