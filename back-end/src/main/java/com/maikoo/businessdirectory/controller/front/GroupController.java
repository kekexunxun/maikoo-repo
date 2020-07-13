package com.maikoo.businessdirectory.controller.front;

import com.fasterxml.jackson.annotation.JsonView;
import com.maikoo.businessdirectory.factory.GroupFactory;
import com.maikoo.businessdirectory.model.constant.GroupTypeEnum;
import com.maikoo.businessdirectory.model.dto.GroupDTO;
import com.maikoo.businessdirectory.model.dto.PostDTO;
import com.maikoo.businessdirectory.model.dto.ResponseDTO;
import com.maikoo.businessdirectory.model.query.GroupQuery;
import com.maikoo.businessdirectory.model.view.GroupView;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.validation.annotation.Validated;
import org.springframework.web.bind.annotation.RequestAttribute;
import org.springframework.web.bind.annotation.RequestMapping;
import org.springframework.web.bind.annotation.RequestParam;
import org.springframework.web.bind.annotation.ResponseBody;

import javax.servlet.http.HttpServletRequest;
import java.util.List;

@Controller
@RequestMapping("/api/group")
public class GroupController {
    @Autowired
    private GroupFactory groupFactory;

    @RequestMapping("/information")
    public String information(@Validated GroupQuery groupQuery, HttpServletRequest request) {
        request.setAttribute("groupQuery", groupQuery);
        return "forward:/api/group/"+groupQuery.getGroupType().toString().toLowerCase()+"/information";
    }

    @JsonView(GroupView.Class.class)
    @ResponseBody
    @RequestMapping("/class/information")
    public ResponseDTO<GroupDTO> classInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).information(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.School.class)
    @ResponseBody
    @RequestMapping("/school/information")
    public ResponseDTO<GroupDTO> schoolInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).information(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.Country.class)
    @ResponseBody
    @RequestMapping("/country/information")
    public ResponseDTO<GroupDTO> countryInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).information(groupQuery.getGroupId()));
    }

    @JsonView(GroupView.Community.class)
    @ResponseBody
    @RequestMapping("/community/information")
    public ResponseDTO<GroupDTO> communityInformation(@RequestAttribute("groupQuery") GroupQuery groupQuery) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).information(groupQuery.getGroupId()));
    }

    @ResponseBody
    @RequestMapping("/dismiss")
    public ResponseDTO dismiss(GroupQuery groupQuery) {
        groupFactory.getGroup(groupQuery.getGroupType()).remove(groupQuery.getGroupId());
        return new ResponseDTO(200, "解散成功");
    }

    @ResponseBody
    @RequestMapping("/owner/change")
    public ResponseDTO changeOwner(GroupQuery groupQuery) {
        groupFactory.getGroup(groupQuery.getGroupType()).changeOwner(groupQuery);
        return new ResponseDTO(200, "修改成功");
    }

    @JsonView(GroupView.SearchList.class)
    @ResponseBody
    @RequestMapping("/search")
    public ResponseDTO<List<GroupDTO>> search(@RequestParam("search") String key) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.CLASS).selectByKey(key));
    }

    @JsonView(GroupView.SearchList.class)
    @ResponseBody
    @RequestMapping
    public ResponseDTO<List<GroupDTO>> list(@RequestParam(name = "pageNum", defaultValue = "1", required = false) int pageNumber) {
        return new ResponseDTO<>(200, "获取成功", groupFactory.getGroup(GroupTypeEnum.CLASS).selectAll(pageNumber));
    }

    @ResponseBody
    @RequestMapping("/poster")
    public ResponseDTO<PostDTO> poster(GroupQuery groupQuery) {
        return new ResponseDTO(200, "获取成功", groupFactory.getGroup(groupQuery.getGroupType()).sharePost(groupQuery.getGroupId()));
    }
}
