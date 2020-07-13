package com.maikoo.businessdirectory.model;

import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import lombok.Data;

@Data
public class GroupDO {
    private long groupId;
    private String groupName;
    private String groupAvatarUrl;
    private String groupAddrCode;
    private String groupAddrDetail;
    private int groupMemCount;
    private GroupTypeEnum groupType;
}
