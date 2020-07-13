$(function () {
    // 手机号验证
    function mobileCheck(mobile) {
        let mobileReg = /^[1][3,4,5,7,8][0-9]{9}$/;
        if (mobile == "" || mobile.length != 11 || !mobileReg.test(mobile)) {
            return false;
        }
        return true;
    }

    // 精确到小数点后6位的验证
    jQuery.validator.addMethod("SixDigitsAfterDot", function (value, element) {
        let dotArr = value.split('.');
        let dotLenth = dotArr.length == 1 ? 0 : dotArr[1].length;
        return this.optional(element) || (dotLenth <= 6);
    }, "精确到小数点后6位");
    // 精确到小数点后3位的验证
    jQuery.validator.addMethod("ThreeDigitsAfterDot", function (value, element) {
        let dotArr = value.split('.');
        let dotLenth = dotArr.length == 1 ? 0 : dotArr[1].length;
        return this.optional(element) || (dotLenth <= 3);
    }, "精确到小数点后3位");
    // 精确到小数点后2位
    jQuery.validator.addMethod("TwoDigitsAfterDot", function (value, element) {
        let dotArr = value.split('.');
        let dotLenth = dotArr.length == 1 ? 0 : dotArr[1].length;
        return this.optional(element) || (dotLenth <= 2);
    }, "精确到小数点后2位");
    // 手机号验证
    jQuery.validator.addMethod("mobileCheck", function (value, element) {
        let isMobile = true;
        let mobileReg = /^[1][3,4,5,7,8][0-9]{9}$/;
        if (value == "" || value.length != 11 || !mobileReg.test(value)) {
            isMobile = false;
        }
        return this.optional(element) || isMobile;
    }, "手机号码格式错误");

    // SMC、ETH设置表格
    $("#form-system-assetsetting").validate({
        rules: {
            smc2rmb: {
                required: true,
                SixDigitsAfterDot: true
            },
            eth2rmb: {
                required: true,
                SixDigitsAfterDot: true
            },
            smc2rmb_rate: {
                required: true,
                max: 100,
                min: 1
            },
            smc_extract_rate: {
                required: true,
                max: 100,
                min: 1
            },
            lp_date_1: {
                required: true,
                max: 720,
                min: 1
            },
            lp_award_1: {
                required: true,
                max: 100,
                min: 1
            },
            lp_date_2: {
                required: true,
                max: 720,
                min: 1
            },
            lp_award_2: {
                required: true,
                max: 100,
                min: 1
            },
            lp_date_3: {
                required: true,
                max: 720,
                min: 1
            },
            lp_award_3: {
                required: true,
                max: 100,
                min: 1
            },
            lp_date_4: {
                required: true,
                max: 720,
                min: 1
            },
            lp_award_4: {
                required: true
            }
        }
    });
    // 新增会员表格
    $("#form-user-add").validate({
        rules: {
            username: {
                required: true,
            },
            usermobile: {
                required: true,
                mobileCheck: true
            },
            useraccount: {
                required: true
            },
            userpass: {
                required: true
            },
            usertpass: {
                required: true
            }
        }
    });
    // 新增矿机表格
    $("#form-miner-add").validate({
        rules: {
            minermodel: {
                required: true,
            },
            minername: {
                required: true,
            },
            minercf: {
                required: true,
                SixDigitsAfterDot: true
            },
            minerprice: {
                required: true,
                min: 0,
                TwoDigitsAfterDot: true
            }
        }
    });
    // 矿机信息修改
    $("#form-miner-info-change").validate({
        rules: {
            minercf: {
                required: true,
                SixDigitsAfterDot: true
            },
            minerprice: {
                required: true,
                TwoDigitsAfterDot: true
            }
        }
    });
})