package com.maikoo.businessdirectory.model.dto;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.model.view.FlagView;
import lombok.Data;

@Data
public class FlagDTO {
    @JsonView(FlagView.User.class)
    private boolean sqHasUnread;
    @JsonView(FlagView.User.class)
    private boolean txHasUnread;
    @JsonView(FlagView.User.class)
    private boolean tbHasUnread;
    @JsonView(FlagView.User.class)
    private boolean xyHasUnread;

    @JsonView(FlagView.System.class)
    private boolean hasNewMsg;

}
