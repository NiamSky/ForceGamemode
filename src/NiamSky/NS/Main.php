<?php

namespace NiamSky\NS; // Update the namespace to match the one in plugin.yml

use pocketmine\event\Listener;
use pocketmine\plugin\PluginBase;
use pocketmine\utils\TextFormat;
use pocketmine\scheduler\Task;
use pocketmine\player\GameMode;
use czechpmdevs\multiworld\util\WorldUtils;

class Main extends PluginBase implements Listener {

    private $lastWorlds = [];

    public function onEnable(): void {
        $this->getServer()->getPluginManager()->registerEvents($this, $this);

        // Start a task to periodically check for world changes
        $this->getScheduler()->scheduleRepeatingTask(new CheckWorldChangeTask($this), 20); // 20 ticks = 1 second
    }

    public function onPlayerChangeWorld(string $playerName, string $newWorldName) {
        // Check if the player has permission to change gamemode (optional)
        $player = $this->getServer()->getPlayerExact($playerName);
        if ($player !== null && $player->hasPermission("forcegamemode.use")) {
            $player->setGamemode(GameMode::SURVIVAL()); // Set gamemode to survival
        }
    }

    public function updatePlayerWorld(string $playerName, string $newWorldName) {
        $this->lastWorlds[$playerName] = $newWorldName;
    }
}

class CheckWorldChangeTask extends Task {

    private $plugin;

    public function __construct(Main $plugin) {
        $this->plugin = $plugin;
    }

    public function onRun(): void {
        $players = $this->plugin->getServer()->getOnlinePlayers();

        foreach ($players as $player) {
            $playerName = $player->getName();
            $currentWorldName = $player->getWorld() !== null ? $player->getWorld()->getFolderName() : null;

            if (!isset($this->plugin->lastWorlds[$playerName]) || $this->plugin->lastWorlds[$playerName] !== $currentWorldName) {
                $this->plugin->onPlayerChangeWorld($playerName, $currentWorldName);
                $this->plugin->updatePlayerWorld($playerName, $currentWorldName);
            }
        }
    }
}
