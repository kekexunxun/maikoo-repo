package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class GroupApplyDO {
    private long applyId;
    private long userId;
    private long processedUserId;
    private int status;
    private long appliedAt;
    private long processedAt;
}
