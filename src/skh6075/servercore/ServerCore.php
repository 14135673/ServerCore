<?php

namespace skh6075\servercore;

use pocketmine\network\mcpe\protocol\GameRulesChangedPacket;
use pocketmine\plugin\PluginBase;
use pocketmine\Server;
use pocketmine\utils\SingletonTrait;
use skh6075\servercore\command\ServerChatManageCommand;
use skh6075\servercore\command\ServerDropItemCommand;
use skh6075\servercore\command\ServerGameRuleCommand;
use skh6075\servercore\command\ServerMoveManageCommand;
use skh6075\servercore\command\ServerPlayerAttackCommand;
use skh6075\servercore\listener\EventListener;
use function json_encode;
use function json_decode;
use function date_default_timezone_set;
use function date_default_timezone_get;
use function strtolower;
use function file_put_contents;
use function file_get_contents;
use function array_values;
use function array_search;
use const JSON_PRETTY_PRINT;
use const JSON_UNESCAPED_UNICODE;

function convertString(string $name): string{
    return strtolower($name);
}

class ServerCore extends PluginBase{
    use SingletonTrait;

    public static string $prefix = "§l§b[관리]§r§7 ";

    protected array $db = [];

    /** @var string[] */
    public const GAMERULES = [
        "showcoordinates"
    ];


    public function onLoad(): void{
        self::setInstance($this);

        if (date_default_timezone_get() !== $timezone = "Asia/Seoul") {
            date_default_timezone_set($timezone);
        }
    }

    public function onEnable(): void{
        $this->saveResource("config.json");
        $this->db = json_decode(file_get_contents($this->getDataFolder() . "config.json"), true);

        $this->getServer()->getCommandMap()->registerAll(strtolower($this->getName()), [
            new ServerDropItemCommand($this),
            new ServerPlayerAttackCommand($this),
            new ServerGameRuleCommand($this),
            new ServerMoveManageCommand($this),
            new ServerChatManageCommand($this)
        ]);
        $this->getServer()->getPluginManager()->registerEvents(new EventListener($this), $this);
    }

    public function onDisable(): void{
        file_put_contents($this->getDataFolder() . "config.json", json_encode($this->db, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));
    }

    public function isDropItemWorld(string $name): bool{
        return array_search(convertString($name), $this->db["drop-worlds"]) !== false;
    }

    public function addDropItemWorld(string $name): bool{
        if (!$this->isDropItemWorld($name)) {
            $this->db["drop-worlds"][] = convertString($name);
            return true;
        }
        return false;
    }

    public function deleteDropItemWorld(string $name): bool{
        if ($this->isDropItemWorld($name)) {
            unset($this->db["drop-worlds"][array_search(convertString($name), $this->db["drop-worlds"])]);
            $this->db["drop-worlds"] = array_values($this->db["drop-worlds"]);
            return true;
        }
        return false;
    }

    public function getDropItemWorlds(): array{
        return $this->db["drop-worlds"];
    }

    public function isPvpWorld(string $name): bool{
        return array_search(convertString($name), $this->db["pvp-worlds"]) !== false;
    }

    public function addPvpWorld(string $name): bool{
        if (!$this->isPvpWorld($name)) {
            $this->db["pvp-worlds"][] = convertString($name);
            return true;
        }
        return false;
    }

    public function deletePvpWorld(string $name): bool{
        if ($this->isPvpWorld($name)) {
            unset($this->db["pvp-worlds"][array_search(convertString($name), $this->db["pvp-worlds"])]);
            $this->db["pvp-worlds"] = array_values($this->db["pvp-worlds"]);
            return true;
        }
        return true;
    }

    public function getPvpWorlds(): array{
        return $this->db["pvp-worlds"];
    }

    public function getGameRuleValue(string $gamerule): bool{
        return $this->db["gamerule"][$gamerule] ?? false;
    }

    public function setGameRuleValue(string $gamerule, bool $value, bool $send = false): void{
        $this->db["gamerule"][$gamerule] = $value;

        if ($send)
            $this->sendUpdatedGamerule($this->getServer()->getOnlinePlayers(), self::GAMERULES);
    }

    public function sendUpdatedGamerule($players, array $gamerules = []): void{
    }

    public function canChatting(): bool{
        return $this->db["chatting"];
    }

    public function setChatting(bool $value): void{
        $this->db["chatting"] = $value;
    }

    public function canMovement(): bool{
        return $this->db["movement"];
    }

    public function setMovement(bool $value): void{
        $this->db["movement"] = $value;
    }
}
