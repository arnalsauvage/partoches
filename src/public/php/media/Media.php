<?php
require_once dirname(__DIR__, 3) . "/autoload.php";
require_once PHP_DIR . "/lib/utilssi.php";

/**
 * Entité Media (POPO - Plain Old PHP Object)
 * Représente un média associé à une chanson.
 */
class Media
{
    const D_M_Y = "d/m/Y";

    private int $id = 0;
    private string $type = "";
    private string $titre = "";
    private string $image = "";
    private int $auteur = 1;
    private string $lien = "";
    private string $description = "";
    private string $tags = "";
    private string $datePub = "";
    private int $hits = 0;

    public function __construct(array $data = [])
    {
        $this->datePub = convertitDateJJMMAAAAversMySql(date(self::D_M_Y));
        
        if (!empty($data)) {
            $this->hydrate($data);
        }
    }

    private function hydrate(array $data): void
    {
        $mapping = [
            'id'          => fn(mixed $v) => $this->setId((int) $v),
            'type'        => fn(mixed $v) => $this->setType((string) $v),
            'titre'       => fn(mixed $v) => $this->setTitre((string) $v),
            'image'       => fn(mixed $v) => $this->setImage((string) $v),
            'auteur'      => fn(mixed $v) => $this->setAuteur((int) $v),
            'lien'        => fn(mixed $v) => $this->setLien((string) $v),
            'description' => fn(mixed $v) => $this->setDescription((string) $v),
            'tags'        => fn(mixed $v) => $this->setTags((string) $v),
            'datePub'     => fn(mixed $v) => $this->setDatePub((string) $v),
            'hits'        => fn(mixed $v) => $this->setHits((int) $v),
        ];

        foreach ($mapping as $key => $setter) {
            if (array_key_exists($key, $data)) {
                $setter($data[$key]);
            }
        }
    }

    // Getters et Setters purs
    public function getId(): int { return $this->id; }
    public function setId(int $id): void { if ($id > 0) $this->id = $id; }

    public function getType(): string { return $this->type; }
    public function setType(string $type): void { $this->type = $type; }

    public function getTitre(): string { return $this->titre; }
    public function setTitre(string $titre): void { $this->titre = $titre; }

    public function getImage(): string { return $this->image; }
    public function setImage(string $image): void { $this->image = $image; }

    public function getAuteur(): int { return $this->auteur; }
    public function setAuteur(int $auteur): void { if ($auteur > 0) $this->auteur = $auteur; }

    public function getLien(): string { return $this->lien; }
    public function setLien(string $lien): void { $this->lien = $lien; }

    public function getDescription(): string { return $this->description; }
    public function setDescription(string $description): void { $this->description = $description; }

    public function getTags(): string { return $this->tags; }
    public function setTags(string $tags): void { $this->tags = $tags; }

    public function getDatePub(): string { return $this->datePub; }
    public function setDatePub(string $datePub): void { $this->datePub = $datePub; }

    public function getHits(): int { return $this->hits; }
    public function setHits(int $hits): void { if ($hits >= 0) $this->hits = $hits; }
}
