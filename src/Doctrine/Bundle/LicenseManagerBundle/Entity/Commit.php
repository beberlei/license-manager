<?php
namespace Doctrine\Bundle\LicenseManagerBundle\Entity;

use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity
 * @ORM\Table(name="commits")
 */
class Commit
{
    /** @ORM\Id @ORM\GeneratedValue @ORM\Column(type="integer") */
    protected $id;
    /** @ORM\Column */
    protected $sha1;
    /** @ORM\ManyToOne(targetEntity="Project") */
    protected $project;
    /** @ORM\ManyToOne(targetEntity="Author", inversedBy="commits") */
    protected $author;

    /** @ORM\Column(type="datetime") */
    protected $created;

    /** @ORM\Column(type="boolean") */
    protected $trivial = false;

    /** @ORM\Column(type="integer") */
    protected $filesChanged;
    /** @ORM\Column(type="integer") */
    protected $insertions;
    /** @ORM\Column(type="integer") */
    protected $deletions;

    public function __construct($sha1, Project $project = null, Author $author = null, $changeLine, \DateTime $created)
    {
        $this->sha1    = $sha1;
        $this->project = $project;
        $this->author  = $author;
        $this->created = $created;

        if (preg_match('(([0-9]+) files changed, ([0-9]+) insertions\(\+\), ([0-9]+) deletions)', $changeLine, $match)) {
            $this->filesChanged = $match[1];
            $this->insertions   = $match[2];
            $this->deletions    = $match[3];
        } else {
            throw new \InvalidArgumentException("Invalid changeline could not be parsed: " . $changeLine);
        }
    }

    /**
     * Get id.
     *
     * @return id.
     */
    public function getId()
    {
        return $this->id;
    }

    /**
     * Get sha1.
     *
     * @return sha1.
     */
    public function getSha1()
    {
        return $this->sha1;
    }

    /**
     * Get project.
     *
     * @return project.
     */
    public function getProject()
    {
        return $this->project;
    }

    /**
     * Get author.
     *
     * @return author.
     */
    public function getAuthor()
    {
        return $this->author;
    }

    /**
     * Get deletions.
     *
     * @return deletions.
     */
    public function getDeletions()
    {
        return $this->deletions;
    }

    /**
     * Get insertions.
     *
     * @return insertions.
     */
    public function getInsertions()
    {
        return $this->insertions;
    }

    /**
     * Get filesChanged.
     *
     * @return filesChanged.
     */
    public function getFilesChanged()
    {
        return $this->filesChanged;
    }

    public function getCreated()
    {
        return $this->created;
    }

    public function getTrivial()
    {
        return $this->trivial;
    }

    public function markTrivial()
    {
        $this->trivial = true;
    }
}

