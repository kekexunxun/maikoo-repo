package com.maikoo.businessdirectory.model;

import lombok.Data;

@Data
public class MessageDO {
    private long msgId;
    private String msgContent;
    private String msgTitle;
    private long sendTo;//user_id
    private long sentAt;


}
