package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.ClassGroupInformationQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import com.maikoo.businessdirectory.service.GroupService;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.ResponseBody;

@Controller
@ResponseBody
@RequestMapping("/api/group/class")
public class ClassGroupController {
    private GroupService groupService;

    @Autowired
    public void setGroupService(GroupFactory groupFactory) {
        this.groupService = groupFactory.getGroup(GroupTypeEnum.CLASS);
    }

    @JsonView(GroupView.Insert.class)
    @RequestMapping("/add")
    public ResponseDTO<GroupDTO> insert(ClassGroupInformationQuery classGroupInformationQuery){
        return new ResponseDTO<>(200, "创建成功", groupService.insert(classGroupInformationQuery));
    }

    @RequestMapping("/update")
    public ResponseDTO update(ClassGroupInformationQuery classGroupInformationQuery){
        groupService.update(classGroupInformationQuery);
        return new ResponseDTO(200, "修改成功");
    }
}
