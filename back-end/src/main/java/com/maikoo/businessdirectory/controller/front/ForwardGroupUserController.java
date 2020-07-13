package com.maikoo.businessdirectory.controller.front;

import com.maikoo.businessdirectory.factory.GroupUserFactory;
import com.maikoo.businessdirectory.model.UserDO;
import com.maikoo.businessdirectory.model.query.GroupUserQuery;
import org.springframework.beans.factory.annotation.Autowired;
import org.springframework.stereotype.Controller;
import org.springframework.web.bind.annotation.RequestMapping;

import javax.servlet.http.HttpServletRequest;
import javax.servlet.http.HttpSession;

@Controller
@RequestMapping("/api/group/user")
public class ForwardGroupUserController {
    @Autowired
    private GroupUserFactory groupUserFactory;
    @Autowired
    private HttpSession session;

    @RequestMapping
    public String list(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        groupUserQuery.setRequiredPaging(true);
        request.setAttribute("groupUserQuery", groupUserQuery);
        if (groupUserQuery.isHasDetail()) {
            return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/detail-list";
        }
        return "forward:/api/group/user/list";
    }

    @RequestMapping("/information")
    public String information(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);

        UserDO userDO = (UserDO) session.getAttribute("current_user");
        if (userDO.getUserId() != groupUserQuery.getUserId()) {
            return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/others-information";
        }
        return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/personal-information";
    }

    @RequestMapping("/apply/information")
    public String applyInformation(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/apply/information";
    }

    @RequestMapping("/review/information")
    public String reviewInformation(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/review/information";
    }

    @RequestMapping("/search")
    public String searchGroupUserInfo(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/search";
    }

    @RequestMapping("/member")
    public String isMember(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/api/group/user/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/member";
    }

    @RequestMapping("/confirm")
    public String isApply(GroupUserQuery groupUserQuery, HttpServletRequest request) {
        request.setAttribute("groupUserQuery", groupUserQuery);
        return "forward:/api/group/user/apply/" + groupUserQuery.getGroupType().toString().toLowerCase() + "/confirm";
    }
}
