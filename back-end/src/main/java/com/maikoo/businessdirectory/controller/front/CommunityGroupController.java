package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.CommunityGroupInformationQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import com.maikoo.businessdirectory.service.GroupService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/group/community")
public class CommunityGroupController {
    private GroupService groupService;

    @Autowired
    public void setGroupService(GroupFactory groupFactory) {
        this.groupService = groupFactory.getGroup(GroupTypeEnum.COMMUNITY);
    }

    @JsonView(GroupView.Insert.class)
    @RequestMapping("/add")
    public ResponseDTO<GroupDTO> insert(CommunityGroupInformationQuery communityGroupInformationQuery){
        return new ResponseDTO<>(200, "创建成功", groupService.insert(communityGroupInformationQuery));
    }

    @RequestMapping("/update")
    public ResponseDTO update(CommunityGroupInformationQuery communityGroupInformationQuery){
        groupService.update(communityGroupInformationQuery);
        return new ResponseDTO(200, "修改成功");
    }
}
