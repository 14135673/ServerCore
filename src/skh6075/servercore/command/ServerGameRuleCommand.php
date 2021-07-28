<?php

namespace skh6075\servercore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use skh6075\servercore\ServerCore;

class ServerGameRuleCommand extends Command{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        parent::__construct("gamerule", "gamerule 명령어 입니다.");
        $this->setPermission("server.gamerule.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $label, array $args): bool{
        if (!$this->testPermission($player)) {
            $player->sendMessage(ServerCore::$prefix . "당신은 이 명령어를 사용할 권한이 없습니다.");
            return false;
        }
        $gamerule = array_shift($args) ?? "";
        $value = array_shift($args) ?? false;
        $send = array_shift($args) ?? true;
        if (trim($gamerule) !== "" and in_array($gamerule, ServerCore::GAMERULES)) {
            $this->plugin->setGameRuleValue($gamerule, $value, $send);
            $player->sendMessage(ServerCore::$prefix . "성공적으로 게임룰을 업데이트하였습니다.");
        } else {
            $player->sendMessage(ServerCore::$prefix . "/gamerule [gamerule] [value: true:false] [send: true:false]");
        }
        return true;
    }
}