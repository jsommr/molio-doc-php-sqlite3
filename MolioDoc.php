<?php

use Ramsey\Uuid\UuidInterface;

class MolioDoc {
    private SQLite3 $db;

    function __construct(SQLite3 $db) {
        $this->db = $db;
    }

    function addBygningsdelsbeskrivelse(Bygningsdelsbeskrivelse $bygningsdelsbeskrivelse): int {
        $stmt = $this->db->prepare('
            insert into bygningsdelsbeskrivelse (
                name,
                bygningsdelsbeskrivelse_guid,
                basisbeskrivelse_version_guid
            ) values (:name, :bygningsdelsbeskrivelse_guid, :basisbeskrivelse_version_guid)
        ');
        $stmt->bindValue(':name', $bygningsdelsbeskrivelse->name);
        $stmt->bindValue(':bygningsdelsbeskrivelse_guid', $bygningsdelsbeskrivelse->bygningsdelsbeskrivelse_guid->getBytes(), SQLITE3_BLOB);
        $stmt->bindValue(':basisbeskrivelse_version_guid', $bygningsdelsbeskrivelse->basisbeskrivelse_version_guid->getBytes(), SQLITE3_BLOB);
        $result = $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    function addBygningsdelsbeskrivelseSection(BygningsdelsbeskrivelseSection $bygningsdelsbeskrivelseSection): int {
        $stmt = $this->db->prepare('
            insert into bygningsdelsbeskrivelse_section (
                bygningsdelsbeskrivelse_id,
                section_no,
                heading,
                text,
                molio_section_guid,
                parent_id
            ) values (
                :bygningsdelsbeskrivelse_id,
                :section_no,
                :heading,
                :text,
                :molio_section_guid,
                :parent_id
            )
        ');
        $stmt->bindValue(':bygningsdelsbeskrivelse_id', $bygningsdelsbeskrivelseSection->bygningsdelsbeskrivelseId);
        $stmt->bindValue(':section_no', $bygningsdelsbeskrivelseSection->sectionNo, SQLITE3_INTEGER);
        $stmt->bindValue(':heading', $bygningsdelsbeskrivelseSection->heading);
        $stmt->bindValue(':text', $bygningsdelsbeskrivelseSection->text);
        $stmt->bindValue(':molio_section_guid', $this->getNullableGuidBytes($bygningsdelsbeskrivelseSection->molioSectionGuid), SQLITE3_BLOB);
        $stmt->bindValue(':parent_id', $bygningsdelsbeskrivelseSection->parentId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    function addAttachment(Attachment $attachment): int {
        $stmt = $this->db->prepare('
            insert into attachment (name, mime_type, content)
            values (:name, :mime_type, :content)
        ');
        $stmt->bindValue(':name', $attachment->name);
        $stmt->bindValue(':mime_type', $attachment->mimeType);
        $stmt->bindValue(':content', $attachment->content, SQLITE3_BLOB);
        $result = $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    function addBygningsdelsbeskrivelseSectionAttachment(int $bygningsdelsbeskrivelseSectionId, int $attachmentId): int {
        $stmt = $this->db->prepare('
            insert into bygningsdelsbeskrivelse_section_attachment (bygningsdelsbeskrivelse_section_id, attachment_id)
            values (:bygningsdelsbeskrivelse_section_id, :attachment_id)
        ');
        $stmt->bindValue(':bygningsdelsbeskrivelse_section_id', $bygningsdelsbeskrivelseSectionId, SQLITE3_INTEGER);
        $stmt->bindValue(':attachment_id', $attachmentId, SQLITE3_INTEGER);
        $result = $stmt->execute();
        return $this->db->lastInsertRowID();
    }

    function getNullableGuidBytes(?UuidInterface $guid): ?string {
        return $guid ? $guid->getBytes() : null;
    }
}

class Attachment {
    public string $name;
    public string $mimeType;
    public string $content;

    function __construct(string $name, string $mimeType, string $content) {
        $this->name = $name;
        $this->mimeType = $mimeType;
        $this->content = $content;
    }
}

class Bygningsdelsbeskrivelse {
    public string $name;
    public UuidInterface $bygningsdelsbeskrivelse_guid;
    public UuidInterface $basisbeskrivelse_version_guid;
}

class BygningsdelsbeskrivelseSection {
    public int $bygningsdelsbeskrivelseId;
    public int $sectionNo;
    public string $heading;
    public string $text = '';
    public ?UuidInterface $molioSectionGuid = null;
    public ?int $parentId = null;

    function __construct(int $bygningsdelsbeskrivelseId, int $sectionNo, string $heading) {
        $this->bygningsdelsbeskrivelseId = $bygningsdelsbeskrivelseId;
        $this->sectionNo = $sectionNo;
        $this->heading = $heading;
    }
}