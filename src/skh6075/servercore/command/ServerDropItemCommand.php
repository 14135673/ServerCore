<?php

namespace skh6075\servercore\command;

use pocketmine\command\Command;
use pocketmine\command\CommandSender;
use skh6075\servercore\ServerCore;

class ServerDropItemCommand extends Command{

    protected ServerCore $plugin;


    public function __construct(ServerCore $plugin) {
        parent::__construct("드랍월드", "드랍월드 명령어 입니다.");
        $this->setPermission("server.dropitem.permission");
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
                    $player->sendMessage(ServerCore::$prefix . "/드랍월드 추가 [월드명]");
                    return false;
                }
                if ($this->plugin->addDropItemWorld($name)) {
                    $player->sendMessage(ServerCore::$prefix . "§f" . $name . "§r§7 월드를 추가하였습니다.");
                } else {
                    $player->sendMessage(ServerCore::$prefix . "이미 존재하는 월드 입니다.");
                }
                break;
            case "제거":
                if (trim($name = array_shift($args) ?? "") === "") {
                    $player->sendMessage(ServerCore::$prefix . "/드랍월드 제거 [월드명]");
                    return false;
                }
                if ($this->plugin->deleteDropItemWorld($name)) {
                    $player->sendMessage(ServerCore::$prefix . "§f" . $name . "§r§7 월드를 삭제하였습니다.");
                } else {
                    $player->sendMessage(ServerCore::$prefix . "존재하지 않는 월드 입니다.");
                }
                break;
            case "목록":
                $player->sendMessage(ServerCore::$prefix . "드랍템 월드 목록 (§f" . count($this->plugin->getDropItemWorlds()) . "개§7) : §f" . implode(", ", $this->plugin->getDropItemWorlds()));
                break;
            default:
                foreach ([
                    ["/드랍월드 추기 [월드명]", "드랍아이템 가능 월드를 추가합니다."],
                    ["/드랍월드 제거 [월드명]", "드랍아이템 가능 월드를 삭제합니다."],
                    ["/드랍월드 목록", "드랍아이템 가능 월드 목록을 봅니다."]
                ] as $value)
                    $player->sendMessage(ServerCore::$prefix . $value[0] . " | " . $value[1]);
                break;
        }
        return true;
    }
}