<?php

namespace skh6075\servercore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use skh6075\servercore\ServerCore;

class ServerPlayerAttackCommand extends Command{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        parent::__construct("pvp월드", "pvp월드 명령어 입니다.");
        $this->setPermission("server.pvp.permission");
        $this->plugin = $plugin;
    }

    public function execute(CommandSender $player, string $label, array $args): bool{
        if (!$this->testPermission($player)) {
            $player->sendMessage(ServerCore::$prefix . "당신은 이 명령어를 사용할 권한이 없습니다.");
            return false;
        }
        switch (array_shift($args) ?? "") {
            case "추가":
                if (trim($name = array_shift($args) ?? "") === "") {
                    $player->sendMessage(ServerCore::$prefix . "/pvp월드 추가 [월드명]");
                    return false;
                }
                if ($this->plugin->addPvpWorld($name)) {
                    $player->sendMessage(ServerCore::$prefix . "§f" . $name . "§r§7 월드의 전투를 허용하였습니다.");
                } else {
                    $player->sendMessage(ServerCore::$prefix . "해당 월드는 이미 전투허용 월드 입니다.");
                }
                break;
            case "제거":
                if (trim($name = array_shift($args) ?? "") === "") {
                    $player->sendMessage(ServerCore::$prefix . "/pvp월드 제거 [월드명]");
                    return false;
                }
                if ($this->plugin->deletePvpWorld($name)) {
                    $player->sendMessage(ServerCore::$prefix . "§f" . $name . "§r§7 월드의 전투를 비허용하엿습니다.");
                } else {
                    $player->sendMessage(ServerCore::$prefix . "해당 월드는 이미 전투 비허용 월드 입니다.");
                }
                break;
            case "목록":
                $player->sendMessage(ServerCore::$prefix . "pvp 허용 월드 목록 (§f" . count($this->plugin->getPvpWorlds()) . "개§7) : §f" . implode(", ", $this->plugin->getPvpWorlds()));
                break;
            default:
                foreach ([
                    ["/pvp월드 추가 [월드명]", "전투 허용 월드를 추가합니다."],
                    ["/pvp월드 제거 [월드명]", "전투 허용 월드를 제거합니다."],
                    ["/pvp월드 목록", "전투 허용 월드 목록을 봅니다."]
                ] as $value)
                    $player->sendMessage(ServerCore::$prefix . $value[0] . " | " . $value[1]);
                break;
        }
        return true;
    }
}