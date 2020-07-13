小程序后台相关参数介绍
    
1. AccountType 
    0 用户 
    1 票券核销员 
    2 系统管理员 
    3 总管理员
2. 商品分类
    0 票券 - 全部 （这一条直接忽略 这条不要显示在商品分类界面）
    1 票券 - 经典 
    2 票券 - 尝鲜
    3 票券 - 刺激
    4 票券 - 休闲
    5 商城 - 生活用品
3. 促销活动ID自1开始自增。商品促销活动ID为0则表示该商品不参与促销活动
4. 订单状态说明 对应订单表(order) state字段
    0 待付款
    1 待发货
    2 已发货
    3 待评价
    4 已完成
    5 已取消
    6 售后申请


Cache 参数简介

1. adminAccountInfo 存放管理员账号相关信息，其包含内容有：
    user_openid         用户openid
    user_id             用户id
    accountType         管理员类别
2. userAccountInfo 存放用户相关信息，其包含内容为： 
    user_openid         用户的openid
    isAuth              用户是否实名认证
    isAdmin             用户是否为管理员
    userInfo            用户信息（微信）
    rebate              用户的返利
    userID              用户ID
    identID             用户身份ID
    telNum              用户电话
3. shopGoodsCount    系统商品总数，是一个键值对
    id                  分类id          key
    count               分类计数        value
4. userDistriInfo 存放用户分销管理的缓存，其包含内容为
    goodsid             当前商品id
    userid              当前用户id
    parentid            当前用户上级id
    grandid             当前用户上上级id
5. promotionInfo 存放有效的促销活动，其包含内容为
    promotionid         当前活动id
    count               当前活动折扣
    name                当前活动名称
    start_time          当前活动开始时间
    end_time            当前活动结束时间
    last_paused_time    最近一次暂停的时间
    last_continue_time  最近一次恢复的时间
6. userCartInfo 存放用户购物车信息
    godosInfo           商品详情
    user_openid         用户openid
7. shopGoodsInfo 存放系统商品缓存
    goods_id            商品id
    name                商品名称
    price               商品价格
    shop_price          商品售价
    pic                 商品图片
    promotion_id        参加的活动id
8. activityUser 存放当前参与活动的用户ID （短暂，约30s）
    user_id             用户id
    user_openid         用户openid
    activity_id         活动id






工作格式

#### 2018-05-07 v1.0 author

    1. A 新增XXX功能...
    2. U 更新XXX功能...
    3. F 修复XXX功能...

#### 2018-05-29 v1.16 方振杰

    1. A 新增新项目(环境参数数据库配置等)
    2. U 优化用户管理-管理员管理(添加管理员时权限分配)
    3. U 优化后台index控制器代码
 