Name: Zhengxu Xia
Email: zxxia@ucla.edu

Name: Zhenli Jiang
Email:jenny921111@gmail.com

Buffer Allocation:

BTLeafNode                        +--------(key, ptr) pair-------+ 
                                  |                              |
+————————————+—————————————————---+------------+-----------------+------------------------+----------------+
|4-byte count| 4-byte nextNodePtr | 4-byte key | 8-byte RecordId |    ...                 | 8 unused bytes |
+————————————+————----------------+------------+-----------------+------------------------+----------------+
|                                 |                                                       |                |
|                                 +----------------Max 84 (key, value) pairs--------------+                |
+-----------------------------1024-byte buffer-------------------------------------------------------------+



BTNonLeafNode
                          +------(key, pid) pair----+ 
                          |                         |
+————————————+——————------+------------+------------+----------------------------+------------------------+
|4-byte count| 4-byte pid | 4-byte key | 4-byte pid |    ...                     |     344 unused bytes   |
+————————————+————--------+------------+------------+----------------------------+------------------------+
|                         |                                                      |                        |
|                         +-------------------Max 84 (key, pid) pair-------------+                        |
+-----------------------------1024-byte buffer------------------------------------------------------------+



Other info:

1.	Modify contructors of BTLeafNode and BTNonLeafNode, initialize private variable buffer to a char array with
	1024 bytes of 0. Therefore, when a new node is created, 4-byte count in each node is 0.

2.	Index file contains one page which stores rootPid and treeHeight at pid = 0 and the rest pages which store
	nodes. In total, an extra pageread and pagewrite are needed. The illustration is below:

	Pagefile
	     0          1         2         3         4
	+----------+---------+---------+---------+---------+
	|  rootPid |   node  |   node  |  node   |   ....  |  
	|treeHeight|         |         |         |         |
	+----------+---------+---------+---------+---------+


3. We decide to use BTree index file when there exists one or more non-NE conditions on key. However, if the key range
   is too large, traversing sorted rid in leafnode and accessing tuple will result in extra pageread in RecordFile.


4. One day of grace period is used for Project 2D.
